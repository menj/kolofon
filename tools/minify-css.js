/**
 * Emit minified copies of the theme stylesheets.
 *
 * The source files stay readable and commented; the theme enqueues the .min.css
 * when it exists and SCRIPT_DEBUG is off. Run after editing any stylesheet:
 *
 *   node tools/minify-css.js
 */
const csstree = require('css-tree');
const fs = require('fs');
const path = require('path');

const files = ['main.css', 'admin-options.css', 'editor.css'];
let totalOld = 0, totalNew = 0;

for (const name of files) {
  const src = path.join('assets/css', name);
  if (!fs.existsSync(src)) continue;
  const css = fs.readFileSync(src, 'utf8');
  const ast = csstree.parse(css);
  const out = csstree.generate(ast);           // drops comments and whitespace
  const dest = src.replace(/\.css$/, '.min.css');
  fs.writeFileSync(dest, out);
  totalOld += css.length; totalNew += out.length;
  console.log(`  ${name.padEnd(20)} ${String(Math.round(css.length/1024)).padStart(3)} KiB -> ${String(Math.round(out.length/1024)).padStart(3)} KiB`);
}
console.log(`\n  TOTAL ${Math.round(totalOld/1024)} KiB -> ${Math.round(totalNew/1024)} KiB (saved ${Math.round((totalOld-totalNew)/1024)} KiB, ${Math.round((1-totalNew/totalOld)*100)}%)`);
