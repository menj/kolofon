# Design and standards audit

Catches the defect classes that are invisible in code review and only show up
when someone stares at a screenshot: declarations the browser silently discards,
rules that quietly override each other, text crowded against a container edge,
and controls too small to hit.

These are development tools. They need Node and Python packages that the theme
itself does not ship or depend on.

## Setup

```
npm install css-tree axe-core
pip install playwright && playwright install chromium
```

## css-validate.js

Validates every stylesheet against the CSS specification with `css-tree`.

```
node tests/audit/css-validate.js assets/css/*.css
```

Reports syntax errors and invalid property values. Custom properties and any
value containing `var()` are excluded deliberately: the first are valid by
definition, and the second cannot be resolved statically, so counting either
would produce hundreds of false positives.

## css-audit.js

Finds declarations that never take effect.

```
node tests/audit/css-audit.js assets/css/*.css
```

Two checks:

- **Same-context conflicts.** The same property set twice on the same selector
  within the same at-rule context, with different values. The audit is media
  aware, so a responsive override inside `@media` is not reported, and a plain
  value followed by one using `clamp()`, `min()`, `max()`, `color-mix()` or
  `var()` is recognised as a deliberate fallback.
- **Padding discarded by `border-collapse: collapse`.** A collapsed table
  ignores its own padding (CSS 2.1 17.6.1). This shipped once: the options form
  had a 28px inset that the browser threw away, leaving labels 3px from the
  panel edge.

## layout-audit.py

Measures rendered geometry in a real browser.

```
python3 tests/audit/layout-audit.py
```

Reports crowding (text within 8px of a container edge), sibling misalignment of
1 to 6px, targets under 24 by 24 CSS pixels, and horizontal overflow, at both
desktop and mobile widths.

### Known exemptions

The geometry report is a triage list, not a defect list. These are expected:

| Finding | Why it is not a defect |
| --- | --- |
| `textarea` at 0px from its cell edge | A form control filling its cell is intended |
| Hidden toggle `input` at 1x1, offset 1px | Visually hidden; the `label` is the real target and measures 105x24 |
| `.skip-link` at 0px from the top | Positioned off-screen until focused |
| `.post-tags-label` at 0px from the left | Flex row starting at the content edge, no inset intended |
| Nav and site-title links under 24px tall | Exempt under WCAG 2.2 SC 2.5.8 via the spacing exception; verify centres stay 24px apart if the menu tightens |

Anything outside this table is a regression. Check the spacing exception with
the centre-distance test before deciding a small target is acceptable.
