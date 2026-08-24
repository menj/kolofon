# Kolofon

Modernist, minimalist WordPress theme for the personal writer's microsite at
[menj.blog](https://menj.blog).

- Classic PHP templates with hybrid block-editor support
- Tabbed Theme Options with six tabs (Identity, Layout, Social, Fediverse,
  Advanced, Documentation)
- Mutually exclusive sections: one category per post, enforced server-side
- Three colour schemes: Charcoal (default), Ivory, Auto
- Five font stacks including two self-hosted webfonts loaded only when
  selected
- Paginated Blog Index rendered as a year-anchored ledger
- 38-platform social registry driving profile icons, JSON-LD `sameAs`, and a
  per-post share row (X, Facebook, LinkedIn, Bluesky, Reddit, Telegram,
  WhatsApp, email, copy-link) — one registry, three consumers
- A separate freeform field for non-social sameAs pages (Gravatar, Wikipedia,
  Crunchbase) that don't belong in the icon-driven social registry
- Open Graph and JSON-LD that stand down when an SEO plugin is active
- Guarded `wp_head`/`wp_footer`: a plugin fatal is caught and logged instead
  of taking the front end down
- Email harvesting protection, aggressive security posture, no comments,
  no XML-RPC
- Translation ready
- Playwright smoke tests

## Requirements

- WordPress 6.4 or newer
- PHP 8.0 or newer

## Install

Download the latest release zip from
[Releases](https://github.com/menj/kolofon/releases), then upload it in
WordPress under **Appearance > Themes > Add New > Upload Theme**. The theme
auto-provisions a Blog Index page at `/blog` on activation.

## Documentation

Full documentation lives at [`docs/`](./docs/):

- [`readme.md`](./docs/guides/readme.md) — overview, directory layout, theme options,
  font stacks, extension surface
- [`upgrading.md`](./docs/guides/upgrading.md) — roadmap, phase status, versioning
  policy, historical decisions
- [`ssot.md`](./docs/reference/ssot.md) — single source of truth: hard invariants,
  authority map, option keys
- [`changelog.md`](./docs/reference/changelog.md) — full release history
- [`now-feature-spec.yml`](./docs/specs/now-feature-spec.yml) — historical
  specification for the original Now feature

These render inline on the theme's own Documentation tab under **Appearance >
Theme Options** once installed.

## Development

CSS and JavaScript are hand-written; no bundler or preprocessor. The one build
artefact is the minified CSS beside each stylesheet, which `inc/enqueue.php`
serves in preference unless `SCRIPT_DEBUG` is on.

```
make install    Install development dependencies
make syntax     Fast php -l sweep
make lint       PHPCS against the WordPress standard
make fix        Auto-fix what PHPCS can fix
make release    Build a distributable zip
```

Pushing a tag builds the release automatically. The workflow refuses to run if
the tag disagrees with the `Version:` header in `style.css`.

## Licence

GPL v2 or later. Forked from the
[Chris Wiegman Theme](https://github.com/ChrisWiegman/chriswiegman-theme)
by Chris Wiegman.
