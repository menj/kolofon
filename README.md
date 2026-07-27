# Kolofon

Modernist, minimalist WordPress theme for the personal writer's microsite at
[menj.bio](https://menj.bio/).

- Classic PHP templates with hybrid block-editor support
- Tabbed Theme Options with eight tabs (Identity, Sections, Social,
  Appearance, Layout, Advanced, System, Documentation)
- Mutually exclusive sections: one category per post, enforced server-side
- Three colour schemes: Charcoal (default), Ivory, Auto
- Five font stacks including two self-hosted webfonts loaded only when
  selected
- Open Graph and JSON-LD that stand down when an SEO plugin is active
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

All four render inline on the theme's own Documentation tab under **Appearance
> Theme Options** once installed.

## Development

No asset build step. CSS and JavaScript are hand-written.

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
