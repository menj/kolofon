# Boot test

A fast PHP check that the theme loads, activates, and boots through the
WordPress lifecycle without a fatal. It needs no database, no web server, and
no WordPress install; it runs against a set of stubs in plain PHP.

## Running

```bash
php tests/boot/boot-test.php
```

Exit code is 0 on pass, 1 on any failure, so it drops straight into a build or
a pre-commit hook.

## What it checks

- The theme loads through `functions.php` in the real module order with no
  include-time fatal.
- The full lifecycle fires without a fatal: `after_setup_theme`, `init`,
  `after_switch_theme` (the activation hook), `widgets_init`,
  `wp_enqueue_scripts`, `rest_api_init`, `admin_menu`, `admin_init`, `wp_head`,
  `wp_footer`.
- Key filters run: `the_content`, `wp_robots`, `body_class`,
  `kolofon_font_stacks`, `kolofon_colour_presets`.
- Every registered hook callback is callable. A callback that names a function
  which does not exist passes PHP lint and PHPCS but fatals the moment its hook
  fires on a real site; this catches it.
- Every options field type renders without a fatal.
- All defaults round-trip through the sanitiser with no key lost.
- The typography builders execute (font stacks, colour presets, webfont faces).

## What it does NOT prove

The stubs fake WordPress. Conditionals return fixed values, queries return
empty, escaping is identity, and there is no database. So a green boot test
proves the theme **runs**; it does not prove any feature **behaves correctly**.
It cannot tell you whether the blog index groups posts by year, whether the
section chooser filters correctly, or whether the tag list appears on a real
post. Those need a live install. See `docs/guides/upgrading.md`, Open items, Priority
1.

## Why this exists

It fills the gap between the two other gates. PHPCS only reads the code; it
cannot see a hook pointing at a missing function. The Playwright smoke suite in
`tests/e2e/` needs a running WordPress. This boot test catches the
fatal-on-activation class of bug, the one that white-screens a site the moment
the theme is switched on, with nothing but PHP.

## Files

- `boot-test.php` — the runner and its assertions.
- `wordpress-stubs.php` — the fake WordPress environment. When the theme starts
  calling a WordPress function that is not stubbed here, the boot test fails
  with an "undefined function" error, which is the signal to add it to the
  no-op list near the foot of that file.
