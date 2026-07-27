"""Geometry audit: catches the defect classes I've been missing by eye."""
from playwright.sync_api import sync_playwright
import sys

JS = r"""
() => {
  const out = {crowding:[], misalign:[], targets:[], overflow:[]};
  const vis = e => { const r=e.getBoundingClientRect(); const s=getComputedStyle(e);
    return r.width>0 && r.height>0 && s.visibility!=='hidden' && s.display!=='none'; };
  const path = e => { let p=e.tagName.toLowerCase();
    if(e.id) p+='#'+e.id; else if(e.className && typeof e.className==='string' && e.className.trim())
      p+='.'+e.className.trim().split(/\s+/).slice(0,2).join('.'); return p; };

  // 1. CROWDING: text sitting too close to a visible container edge (border/background)
  document.querySelectorAll('*').forEach(el => {
    if(!vis(el)) return;
    const s = getComputedStyle(el);
    const hasEdge = parseFloat(s.borderLeftWidth)>0 || parseFloat(s.borderTopWidth)>0
                 || (s.backgroundColor && s.backgroundColor!=='rgba(0, 0, 0, 0)')
                 || s.backgroundImage!=='none';
    if(!hasEdge) return;
    const r = el.getBoundingClientRect();
    // find the first descendant that actually renders text
    const kids=[...el.querySelectorAll('*')].filter(k=>vis(k)&&k.textContent.trim()&&k.children.length===0);
    kids.slice(0,4).forEach(k=>{
      const kr=k.getBoundingClientRect();
      const bl=parseFloat(s.borderLeftWidth)||0, bt=parseFloat(s.borderTopWidth)||0;
      const gapL = kr.left - r.left - bl, gapT = kr.top - r.top - bt;
      if(gapL >= 0 && gapL < 8) out.crowding.push({el:path(el), child:path(k), edge:'left', gap:Math.round(gapL)});
      if(gapT >= 0 && gapT < 4) out.crowding.push({el:path(el), child:path(k), edge:'top', gap:Math.round(gapT)});
    });
  });

  // 2. MISALIGNMENT: sibling blocks that nearly share a left edge but do not
  document.querySelectorAll('*').forEach(p => {
    const kids=[...p.children].filter(vis);
    if(kids.length<2) return;
    const lefts = kids.map(k=>({el:path(k), x:Math.round(k.getBoundingClientRect().left)}));
    for(let i=1;i<lefts.length;i++){
      const d = Math.abs(lefts[i].x - lefts[0].x);
      if(d>0 && d<=6) out.misalign.push({parent:path(p), a:lefts[0].el, b:lefts[i].el, offBy:d});
    }
  });

  // 3. TOUCH TARGETS under 24x24 (WCAG 2.5.8 AA)
  document.querySelectorAll('a,button,input,select,textarea,[role=button]').forEach(el=>{
    if(!vis(el)) return;
    const r=el.getBoundingClientRect();
    if(r.width<24 || r.height<24) out.targets.push({el:path(el), w:Math.round(r.width), h:Math.round(r.height)});
  });

  // 4. HORIZONTAL OVERFLOW
  if(document.documentElement.scrollWidth > window.innerWidth+1)
    out.overflow.push({scrollWidth:document.documentElement.scrollWidth, viewport:window.innerWidth});

  return out;
}
"""

def audit(path, label, width=1280):
    with sync_playwright() as p:
        b=p.chromium.launch(); pg=b.new_page(viewport={'width':width,'height':1000})
        pg.goto('file://'+path); pg.wait_for_timeout(350)
        r=pg.evaluate(JS); b.close()
    print(f"\n===== {label} @ {width}px =====")
    def show(key, title, fmt):
        items=r[key]
        # dedupe
        uniq=[]; seenk=set()
        for i in items:
            k=str(i)
            if k not in seenk: seenk.add(k); uniq.append(i)
        if not uniq: print(f"  {title}: none"); return 0
        print(f"  {title}: {len(uniq)}")
        for i in uniq[:8]: print("     "+fmt(i))
        return len(uniq)
    n=0
    n+=show('crowding','CROWDING (text within 8px of a container edge)',
            lambda i:f"{i['el']} > {i['child']}  {i['edge']} gap {i['gap']}px")
    n+=show('misalign','MISALIGNED siblings (off by 1-6px)',
            lambda i:f"{i['parent']}: {i['a']} vs {i['b']} off by {i['offBy']}px")
    n+=show('targets','TOUCH TARGETS under 24x24 (WCAG 2.5.8)',
            lambda i:f"{i['el']} {i['w']}x{i['h']}")
    n+=show('overflow','HORIZONTAL OVERFLOW',
            lambda i:f"scrollWidth {i['scrollWidth']} > viewport {i['viewport']}")
    return n

total=0
for path,label,w in [('/tmp/page-admin2.html','ADMIN options',1160),
                     ('/tmp/page-charcoal.html','FRONT END charcoal',1280),
                     ('/tmp/page-charcoal.html','FRONT END charcoal',390)]:
    total+=audit(path,label,w)
print(f"\n=== {total} geometry findings ===")
