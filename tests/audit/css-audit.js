let csstree;
try {
  csstree = require('css-tree');
} catch (e) {
  console.error('This audit needs css-tree. Install it first:\n  npm install css-tree\nSee tests/audit/README.md.');
  process.exit(2);
}
const fs = require('fs');

for (const file of process.argv.slice(2)) {
  const ast = csstree.parse(fs.readFileSync(file,'utf8'), {positions:true});
  const seen = new Map();      // "context||selector" -> decls
  const collapsed = new Set();
  const ctx = [];              // enclosing at-rule stack

  csstree.walk(ast, {
    enter(node) {
      if (node.type==='Atrule') ctx.push(node.name+' '+(node.prelude?csstree.generate(node.prelude):''));
      if (node.type!=='Rule') return;
      const sel = csstree.generate(node.prelude);
      const key = (ctx.join(' >> ')||'(root)') + '||' + sel;
      const decls = [];
      csstree.walk(node.block, { visit:'Declaration', enter(d) {
        const v = csstree.generate(d.value).trim();
        decls.push({prop:d.property, value:v, line:d.loc&&d.loc.start.line});
        if (d.property==='border-collapse' && v==='collapse') collapsed.add(key);
      }});
      if (!seen.has(key)) seen.set(key, []);
      seen.get(key).push(...decls);
    },
    leave(node){ if (node.type==='Atrule') ctx.pop(); }
  });

  const real = [];
  for (const [key, decls] of seen) {
    const [context, sel] = key.split('||');
    const byProp = new Map();
    decls.forEach(d=>{ if(!byProp.has(d.prop)) byProp.set(d.prop,[]); byProp.get(d.prop).push(d); });
    for (const [prop, list] of byProp) {
      if (list.length < 2) continue;
      const vals = new Set(list.map(x=>x.value));
      if (vals.size < 2) continue;
      // A plain value followed by one using a newer function is a deliberate
      // fallback (e.g. font-size: 1rem; font-size: clamp(...)). Not a conflict.
      const isFallback = list.length===2 &&
        !/\(/.test(list[0].value) && /(clamp|min|max|color-mix|var)\(/.test(list[1].value);
      if (isFallback) continue;
      real.push({context, sel, prop, list});
    }
  }

  const dead = [];
  for (const [key, decls] of seen) {
    if (!collapsed.has(key)) continue;
    decls.filter(d=>/^padding/.test(d.prop)).forEach(d=>dead.push({key,...d}));
  }

  console.log(`\n${file.split('/').pop()}  (${seen.size} rule contexts)`);
  if (real.length) {
    console.log(`  REAL conflicts (same selector, same media context): ${real.length}`);
    real.forEach(d=>{
      console.log(`    [${d.context}] ${d.sel} { ${d.prop} }`);
      d.list.forEach(x=>console.log(`        line ${x.line}: ${x.value}`));
    });
  } else console.log('  no same-context conflicts');
  if (dead.length) {
    console.log(`  DISCARDED padding on collapsed table: ${dead.length}`);
    dead.forEach(d=>console.log(`    line ${d.line}: ${d.prop}: ${d.value}`));
  } else console.log('  no padding discarded by border-collapse');
}
