let csstree;
try {
  csstree = require('css-tree');
} catch (e) {
  console.error('This audit needs css-tree. Install it first:\n  npm install css-tree\nSee tests/audit/README.md.');
  process.exit(2);
}
const fs = require('fs');
let totalErrors = 0, real = [];

for (const file of process.argv.slice(2)) {
  const css = fs.readFileSync(file, 'utf8');
  const parseErrors = [];
  const ast = csstree.parse(css, { filename: file, positions: true,
    onParseError(e){ parseErrors.push({line:e.line, msg:e.message}); } });

  const valueIssues = [];
  csstree.walk(ast, { visit: 'Declaration', enter(node) {
    // Custom properties are valid by definition; skip.
    if (node.property.startsWith('--')) return;
    // css-tree cannot resolve var(); skip those values (tool limitation, not a CSS error).
    let hasVar = false;
    csstree.walk(node.value, { visit:'Function', enter(f){ if(f.name==='var') hasVar = true; } });
    if (hasVar) return;
    const nameErr = csstree.lexer.checkPropertyName(node.property);
    if (nameErr) {
      if (node.property.startsWith('-webkit-') || node.property.startsWith('-moz-') || node.property.startsWith('-ms-')) return;
      valueIssues.push({line: node.loc && node.loc.start.line, msg: nameErr.message}); return;
    }
    const m = csstree.lexer.matchProperty(node.property, node.value);
    if (m.error) valueIssues.push({line: node.loc && node.loc.start.line,
      msg: `${node.property}: ${m.error.message.split('\n')[0]}`});
  }});

  totalErrors += parseErrors.length;
  real.push([file, parseErrors, valueIssues]);
}

for (const [file, pe, vi] of real) {
  console.log(`\n${file.split('/').pop()}`);
  console.log(`  syntax errors    : ${pe.length}`);
  console.log(`  invalid values   : ${vi.length}`);
  pe.forEach(e=>console.log(`    ERROR line ${e.line}: ${e.msg}`));
  vi.forEach(e=>console.log(`    INVALID line ${e.line}: ${e.msg}`));
}
const totalInvalid = real.reduce((n,[,,v])=>n+v.length,0);
console.log(`\n=== ${totalErrors} syntax errors, ${totalInvalid} invalid values (var()/custom props correctly excluded) ===`);
