# menj.bio theme

Modernist, minimalist WordPress theme for the personal bio microsite at
[menj.bio](https://menj.bio/).

- Classic PHP templates with hybrid block-editor support (`theme.json`, block
  patterns, block styles)
- Tabbed Theme Options under **Appearance, Theme Options** with eight tabs
- Mutually exclusive sections: one category per post, enforced server-side
- Variable colour schemes driven entirely by CSS custom properties
- Hand-written CSS and JavaScript, no build step
- Open Graph and JSON-LD that stand down when an SEO plugin is active
- Email harvesting protection, file editors disabled, security headers, no
  comments, no XML-RPC
- Translation ready
- Responsive across three breakpoints with a progressive-enhancement
  navigation toggle

## Requirements

- WordPress 6.4 or newer
- PHP 8.0 or newer

## Directory layout

```
menj-bio/
├── style.css                 Theme header
├── functions.php             Constants and the inc/ module loader
├── theme.json                Editor palette, typography, spacing
├── header.php                Site chrome, branding, primary navigation
├── footer.php                Footer navigation and text
├── home.php                  Front page: hero, social row, recent posts
├── singular.php              Post and page
├── page-blog.php             Blog Index page template, grouped by year
├── index.php                 Archive and search fallback
├── 404.php
├── searchform.php
├── screenshot.png
├── inc/
│   ├── defaults.php       Single source of truth for defaults and presets
│   ├── hooks.php          Extension surface, filtered sanitiser registry, migration
│   ├── options.php        Tabbed Settings API options page
│   ├── options-schema.php Declarative tabs and fields
│   ├── settings-io.php    Export, import, and status messages
│   ├── setup.php          Theme supports, nav menus, image sizes, widgets
│   ├── branding.php       Custom logo, icon set, bundled brand assets
│   ├── login.php          Branded login screen
│   ├── enqueue.php        Front-end asset registration, portrait preload
│   ├── security.php       Headers, comments, head cleanup, file editors
│   ├── chrome.php         Sidebar or topbar layout, numbered navigation, shortcuts
│   ├── social.php         Platform registry and icon renderer
│   ├── email-guard.php    Email obfuscation, content filter, shortcode
│   ├── post-list.php      Shared post list renderer, previews, pagination
│   ├── sections.php       Section registry, enforcement, chooser
│   ├── tags.php           Tag rendering, section spans, topic index
│   ├── page-states.php    Planned pages, navigation badge, content notice
│   ├── meta.php           Open Graph, schema, SEO plugin detection
│   ├── docs.php           Markdown documentation rendering via Parsedown
│   ├── system-report.php  System tab, theme-owned runtime facts
│   ├── dynamic-css.php    Emits :root custom properties, dark-mode block
│   └── blocks.php         Pattern category and block styles
├── assets/
│   ├── css/
│   │   ├── main.css          Front end
│   │   ├── editor.css        Block editor
│   │   └── admin-options.css Options page
│   ├── js/
│   │   ├── nav-toggle.js     Collapsing navigation
│   │   ├── keyboard-nav.js   Digit shortcuts for sidebar navigation
│   │   ├── email-guard.js    Rebuilds obfuscated mailto links
│   │   ├── single-section.js Single-choice categories in the block editor
│   │   └── admin-options.js  Tab widget, colour picker, media picker
│   └── img/
│       ├── profile.png       Portrait, hero fallback
│       ├── favicon.png       32x32
│       ├── icon-192.png      Android
│       └── apple-touch-icon.png
├── languages/
│   └── menj-bio.pot          Translation template
├── patterns/
│   ├── bio-hero.php
│   ├── bio-card.php
│   ├── link-list.php
│   ├── callout.php
│   └── contact-block.php
├── vendor/
│   └── parsedown/            Runtime dependency, MIT
├── docs/
│   ├── readme.md
│   ├── ssot.md
│   ├── upgrading.md
│   └── changelog.md
└── LICENSE                   Optional for internal builds
```

## Theme options

Everything configurable is controlled from **Appearance, Theme Options**. The
page uses the Settings API with a single stored option, `menj_bio_options`, and
switches tabs client-side so all fields save in one request.

| Tab | Controls |
| --- | --- |
| Identity | Hero eyebrow, heading, body copy, portrait, footer text |
| Sections | Section slugs and order, chooser, tag display, enforcement, adjacent-post scope |
| Social | Email protection mode, then one URL per platform |
| Appearance | Colour scheme, font stack, lede type sizes |
| Layout | Chrome layout, keyboard shortcuts, content width, portrait, list style, hover previews |
| Advanced | Meta output, planned-page labels, export, import, file editor lock, restore defaults |
| System | Read-only report of theme-owned runtime facts |
| Documentation | Renders the four files in `docs/` |

Selecting a colour preset injects `:root` custom properties inline on the front
end and in the block editor. Nothing in the stylesheets hard-codes a colour.

### Colour schemes

Charcoal (default), Ivory, and Auto. Auto renders Ivory when the device prefers light and Charcoal when it prefers dark. Both palettes pass WCAG AA on every colour pair.

### Sections

The theme draws a hard line between two taxonomies:

- **Categories are sections.** Exactly one per post. Where a post lives.
- **Tags are topics.** As many as you like. What a post is about.

That line exists because sections overlap in subject even when they do not
overlap as sections. Without tags as the escape valve there is constant
pressure to assign a second category, and the scheme collapses the first time
someone does.

Configure the Sections tab with your category slugs in display order,
separated by commas or new lines. The field resolves each slug and reports
whether a category exists for it, so a typo surfaces on the settings screen
rather than as a section that quietly fails to appear.

Three behaviours follow, each switchable:

- **One category per post.** Enforced on `save_post`, so it holds for the block
  editor, classic editor, REST, WP-CLI, Quick Edit, and imports alike. When
  several categories are present the theme keeps the first configured section
  in configured order, falling back to the first assigned category. The rule is
  deterministic.
- **Section chooser.** A row of links on the home page and on section archives,
  with the current one marked. These are links to real category archives rather
  than client-side filtering, so each section keeps a shareable, indexable URL,
  per-section feeds work natively at `/category/<slug>/feed/`, and none of it
  needs JavaScript.
- **Scoped adjacent posts.** Previous and next links stay within the current
  section. Without this a reader following one topic is dropped into another at
  the foot of every post.

### Metadata

The theme emits Open Graph, Twitter card, canonical, and description tags, plus
a JSON-LD graph carrying `Person`, `WebSite`, and `BlogPosting` on posts. Social
profiles configured on the Social tab populate `sameAs`. The share image falls
back through featured image, configured portrait, then the bundled portrait.

All of it stands down when an SEO plugin is active. Yoast, Rank Math, All in
One SEO, SEOPress, Slim SEO, and The SEO Framework are detected; the Advanced
tab reports which one owns the output. Emitting a second set of tags is worse
than emitting none, because consumers disagree about which wins.

For a plugin the list does not know, filter it:

```php
add_filter( 'menj_bio_seo_plugin_active', '__return_true' );
```

### Settings portability

The Advanced tab exports every setting as JSON and imports it back. Import
discards unrecognised keys, then runs the same sanitiser the options form uses,
so clamps, enum fallbacks, and escaping apply identically to a file and a form
post. Useful as a backup, since every setting lives in one option row.

### Tag index shortcode

```
[menj_tags]
[menj_tags limit="20"]
```

Renders every in-use tag, sized by post count. Displayed alphabetically after
selection by popularity. See the Sections section above for how tags relate to
sections.

### Email protection

Three modes on the Social tab. The default, JavaScript rebuild, emits no
address and no `mailto:` scheme in the served HTML at all; the anchor carries an
encoded payload that `email-guard.js` decodes on load. HTML entities mode works
without JavaScript. Off emits a plain link.

Protection covers the social icon, any `mailto:` link typed into post content,
and the `[menj_email]` shortcode.

```
[menj_email]
[menj_email address="you@example.com" text="Write to me" class="btn"]
```

No client-side scheme stops a headless browser. This stops the large majority
of harvesters, which are a fetch and a regex.

### Hover previews

Hovering or keyboard-focusing a post row reveals that post's featured image
beside the list. Pure CSS, gated on pointer capability and screen width, and it
honours reduced-motion preferences. Posts without a featured image render no
preview.

## Documentation

All documentation lives in `docs/` as lowercase markdown and renders inside
WordPress on the Documentation tab.

| File | Purpose |
| --- | --- |
| `readme.md` | This file. Overview, layout, options. |
| `ssot.md` | Single source of truth. Where each value is authoritative. |
| `upgrading.md` | Roadmap. Phased plan, decisions, versioning policy. |
| `changelog.md` | Version history and the fork delta. |

Adding another `.md` file makes it appear in the sub-nav automatically, after
the four above, with a label derived from the filename. CI fails the build if
any filename contains an uppercase letter.

## Development

No asset build step. CSS and JavaScript are hand-written and ship as-is, so a
clone runs immediately.

```
make install    Install development dependencies
make syntax     Fast php -l sweep, needs no dependencies
make lint       PHPCS against the WordPress standard
make fix        Auto-fix what PHPCS can fix
make release    Build a distributable zip excluding development files
make clean      Remove dependencies and archives
```

Development files (`phpcs.xml`, `composer.json`, `Makefile`, `.github/`,
`.gitignore`, `.editorconfig`) live in the repository and are excluded from the
release archive. `vendor/parsedown/` is a runtime dependency and ships.

Pushing a tag builds the release automatically. The workflow refuses to run if
the tag disagrees with the `Version:` header in `style.css`.

### Extending

Front-end JavaScript is progressive enhancement only. Every script must leave a
working page when it does not run.

Adding an option means three edits in this order: `get_defaults()`, then the
field registration, then the sanitiser. Adding a social platform means one
entry in `get_social_platforms()` and one matching default; the field loop,
sanitiser, and renderer pick it up with no further changes.

The theme exposes one filter so far, `menj_bio_seo_plugin_active`. A general
hook surface is phase 4 of the roadmap.

## Font stacks

Five options. Slug-to-label map, in radio order on the Appearance tab:

- **The reader** (`editorial`, default). Charter with a Georgia fallback, both system fonts. Loads no webfont.
- **Charter, but loud** (`xcharter`). Self-hosted extension of Bitstream Charter. Four weights.
- **Typed** (`special-elite`). Self-hosted typewriter face. One weight.
- **Office memo** (`typewriter`). Hybrid: system monospace for reading, Special Elite for headings.
- **Plaintext** (`mono`). System monospace end to end. Loads nothing.

The webfont stacks are independent: choosing Charter, but loud loads XCharter; choosing Typed loads Special Elite. Adding a stack means registering it through `menj_bio_font_stacks`, optionally with a `webfont` key.

## Attribution

Forked from the [Chris Wiegman Theme](https://github.com/ChrisWiegman/chriswiegman-theme)
v12.7.0 by Chris Wiegman, GPL v2. The fork retains the security posture,
image-size trimming, comment removal, and head cleanup, and adds the options
page, colour system, hybrid block support, patterns, and the `MENJ\Bio`
namespace. See `changelog.md` for the full delta.

### Third-party

- **[Parsedown](https://github.com/erusev/parsedown) 1.8.0** by Emanuil Rusev,
  MIT. Vendored under `vendor/parsedown/`, used only to render this theme's own
  documentation in the admin.

## License

GNU General Public License v2 or later. See `LICENSE` in the theme root.
