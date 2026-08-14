# Kolofon

Modernist, minimalist WordPress theme for the personal writer's microsite at
[menj.blog](https://menj.blog/). Source: [github.com/menj/kolofon](https://github.com/menj/kolofon).

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
kolofon/
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
├── archive-kolofon_status.php  Status archive, matching the blog page layout
├── screenshot.png
├── checksums.sha256          SHA-256 of every shipped PHP file
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
│   ├── activation.php     Auto-provisions blog index page on theme switch
│   ├── webfonts.php       Self-hosted webfont loading, per active stack
│   ├── syndication.php    RSS feed featured images, fediverse:creator meta
│   ├── migration-notice.php  Post-rename admin notice, one-shot
│   ├── dynamic-css.php    Emits :root custom properties, dark-mode block
│   ├── print-branding.php Print letterhead and MLA citation colophon
│   ├── microblog.php      Microblog and Fediverse loader, identity panel, migrations
│   ├── microblog/         Status post type, composer, timeline, REST, AP bridge
│   ├── activitypub/       Bundled ActivityPub 9.2.0 engine, loaded only when enabled
│   └── blocks.php         Pattern category and block styles
├── assets/
│   ├── css/
│   │   ├── main.css             Front end
│   │   ├── editor.css           Block editor
│   │   ├── admin-base.css       Shared admin tokens and components
│   │   ├── admin-options.css    Options page
│   │   ├── admin-activitypub.css Bundled engine screens
│   │   └── *.min.css            Built by tools/minify-css.js, served in preference
│   ├── js/
│   │   ├── nav-toggle.js     Collapsing navigation
│   │   ├── keyboard-nav.js   Digit shortcuts for sidebar navigation
│   │   ├── email-guard.js    Rebuilds obfuscated mailto links
│   │   ├── single-section.js Single-choice categories in the block editor
│   │   ├── hover-preview.js  Shared preview card that travels between rows
│   │   ├── search-overlay.js Full-screen search
│   │   └── admin-options.js  Tab widget, colour picker, media picker
│   ├── img/
│   │   ├── profile.png       Portrait, hero fallback
│   │   ├── favicon.png       32x32
│   │   ├── icon-192.png      Android
│   │   └── apple-touch-icon.png
│   └── fonts/
│       ├── xcharter/         Bitstream Charter extended, 4 weights, opt-in via stack
│       └── special-elite/    Typewriter face, 1 weight, opt-in via stack
├── tools/
│   └── verify-checksums.php  Integrity check, run on the server
├── languages/
│   └── kolofon.pot          Translation template
├── patterns/
│   ├── bio-hero.php
│   ├── bio-card.php
│   ├── link-list.php
│   ├── callout.php
│   └── contact-block.php
├── vendor/
│   └── parsedown/            Runtime dependency, MIT
├── docs/
│   ├── guides/
│   │   ├── readme.md
│   │   └── upgrading.md
│   ├── reference/
│   │   ├── ssot.md
│   │   └── changelog.md
│   └── specs/
│       └── now-feature-spec.yml
└── LICENSE.md                Licence, attribution, bundled components
```

## Theme options

Everything configurable is controlled from **Appearance, Theme Options**. The
page uses the Settings API with a single stored option, `kolofon_options`, and
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
a JSON-LD `@graph` describing the site and its content. The graph is built for
discoverability by search engines and AI tools, and its nodes are keyed by
`@id` so consumers can resolve the relationships between them.

Every page carries a `Person` node (the site owner, with social profiles as
`sameAs`) and a `WebSite` node (with a `SearchAction` that enables the Google
sitelinks search box). On top of that base, each view adds the node that
describes its own type:

| View | Added node |
| --- | --- |
| Front page | `ProfilePage` about the Person |
| Single post | `BlogPosting` with author, dates, section, image |
| Single page | `WebPage`, or `AboutPage` when the slug or title is "about" |
| Category, tag, date, author archive | `CollectionPage` with an `ItemList` of the posts on the page |
| Blog Index page | `CollectionPage` with its `ItemList` |
| Search results | `SearchResultsPage` |

Every view except the front page also carries a `BreadcrumbList` placing it in
the site hierarchy (Home, then category and post for a post, the page for a
page, or the archive label for an archive).

Beyond the JSON-LD graph, the theme also carries schema.org microdata inline on
its markup, on every element that describes something. The site header is a
`WPHeader`, the footer a `WPFooter`, each navigation an `SiteNavigationElement`,
and the content area a `WebPageElement`. Branding carries an `Organization`.
On the home page the hero is the owner's `Person` node, with the eyebrow as
`jobTitle`, the heading as `name`, the body as `description`, and the portrait
as `image`. Post lists are `ItemList`s and every row is a positioned
`ListItem` wrapping a `BlogPosting` with its headline, date, and abstract.
Archives and the blog page are `CollectionPage`s wrapping those lists. Single
posts carry `BlogPosting` with `datePublished`, `dateModified`, `author`,
`articleSection`, `articleBody`, and `image`, alongside the h-entry
microformat. The search form is a `SearchAction`.

The share image falls back through featured image, configured portrait, then
the bundled portrait.

All of it stands down when an SEO plugin is active. Yoast, Rank Math, All in
One SEO, SEOPress, Slim SEO, and The SEO Framework are detected; the Advanced
tab reports which one owns the output. Emitting a second set of tags is worse
than emitting none, because consumers disagree about which wins.

For a plugin the list does not know, filter it:

```php
add_filter( 'kolofon_seo_plugin_active', '__return_true' );
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

### Print

Printing a post produces a branded document, not a stripped web page. The site
chrome is removed, links reveal their URLs, and page breaks are controlled so
headings do not strand at the foot of a sheet. On top of that, two branded
elements appear only on paper: a letterhead masthead carrying the site identity
at the top, and a colophon at the foot with a citation in MLA 9th
edition form and the source URL. Posts get the full colophon; static pages get
the letterhead alone, since a publication-date citation suits an article rather
than an About page.

The citation follows MLA: an inverted author name, the title in quotes, the site
name italicised, an MLA-form date, and the URL without its scheme, closing with
an access date. Because a display name cannot be split into "Last, First"
reliably, the inverted form is entered directly in a Citation name field on the
Identity tab; if it is left empty, the hero heading is used as written.

The branded elements are printed into the markup by `inc/print-branding.php` via
the_content and hidden on screen, so they are real, styleable elements rather
than fragile CSS-generated content. Nothing is added to the screen layout or the
accessibility tree.

### Microblog and the Fediverse

Two switches on the **Fediverse** tab, both off by default and independent of
each other.

**Microblog** adds a Statuses post type for short, title-less posts, a Post
status button in the toolbar, and the `[kolofon_microblog]` shortcode for
showing a timeline on any page. Statuses carry no title by design, so list views
lead with their text and single views take a dated heading. This is the XFedi
Microblog codebase merged into the theme; no plugin is required.

**Federate statuses** loads the bundled ActivityPub engine, generates your
keypair and opens your Actor, inbox and WebFinger endpoints. Your handle is the
account name joined to this site's domain. There is nothing to register: a site
running ActivityPub is its own Fediverse server. The tab shows the handle and
checks the five conditions that actually stop federation working, HTTPS and
pretty permalinks among them.

Turning federation on runs the engine's activation once, flushing rewrite rules
and registering the delivery schedules. Left off, none of the engine's files are
loaded.

Where statuses appear on the front end is controlled from the same tab:

| Setting | Default | Effect |
| --- | --- | --- |
| Show statuses on the blog | off | Mixes statuses into the main post list and the RSS feed. Off, they stay on their own archive at `/statuses/`. |
| Hide statuses from search engines | off | Adds a noindex header to the status archive. They still federate. |
| Status length limit | 500 | Characters allowed in the composer. |
| Statuses per timeline page | 20 | How many the shortcode shows at a time. |

Switching the microblog on therefore changes nothing about existing pages until
one of these is changed. Individual statuses are always readable at their own
permalink, and the archive at `/statuses/` always exists.

If the standalone ActivityPub plugin is active, the theme defers to it and uses
the plugin's engine rather than the bundled copy.

### Verifying file integrity

Security scanners report theme PHP files as "modified more recently than
wp-config.php, possible backdoors". That is true of every file in every theme
the moment it is installed: `wp-config.php` was written once, and theme files
take a fresh timestamp on extraction. The check cannot tell an install from an
intrusion. Bundling the ActivityPub engine makes it louder, since the theme
carries several hundred more PHP files than it otherwise would.

Rather than silence the warning, the theme ships the means to answer it.
`checksums.sha256` records the SHA-256 of every PHP file in the release, and is
regenerated by the build so it cannot fall out of step. To check an
installation, run this on the server:

```
cd wp-content/themes/kolofon
php tools/verify-checksums.php
```

It reports three things:

| Result | Meaning |
| --- | --- |
| Modified | The file differs from the release |
| Not in the release | A PHP file exists that the release never shipped |
| Missing | A file from the release is gone |

The middle case matters most, because an injected backdoor usually arrives as a
new file, and no timestamp heuristic detects that. Exit status is 0 when
everything matches and 1 otherwise, so the check can be run from cron or as part
of a deployment.

Excluding the theme from the scanner would suppress the warning, and would also
mean never hearing about a file that genuinely had been altered. Backdating
timestamps in the build would silence the scanner by defeating the detection it
exists for. Neither is done.

### Search

Search works without any configuration: WordPress's native search, a results
view in `index.php`, and the form on the results and 404 pages. On top of that,
the header carries a search icon that opens a full-screen overlay, the way the
search on many modern sites behaves. The overlay wraps the theme's own
`get_search_form()`, so it inherits every attribute and label rather than
duplicating the markup.

It is progressive enhancement. The icon and overlay are built by
`assets/js/search-overlay.js` from a `<template>` the header prints; with
JavaScript off, neither appears and search still works through the page forms.
Escape or a backdrop click closes the overlay, focus is trapped inside it while
open, and `Ctrl/Cmd+K` opens it (guarded so it never fires while typing in a
field). The overlay is styled entirely from the theme's colour tokens and
respects reduced-motion.

### Hover previews

Hovering or keyboard-focusing a post row reveals a peek at a fixed anchor at
the top of the list. The list stays compact by default and expands its right
gutter smoothly on hover, so the peek lands in dedicated space rather than
overlapping row text. Moving between rows swaps the peek content but the
anchor stays put — no cursor-chasing layout.

Every row gets a peek. Posts with a featured image show it as a photographic
tile, enforced to a 3:2 landscape aspect regardless of source dimensions.
Posts without a featured image show a typographic tile — the post title
clamped to three lines in the theme's heading font, over a subtle
palette-tinted background — sharing the same anchor and shape. No dead space
for image-less posts, no per-row jitter from mixed behaviour.

Pure CSS, gated on pointer capability and screen width, honours reduced-motion.

## Documentation

Documentation lives in `docs/`, organised into three subfolders so each kind of
document is easy to find:

| File | Purpose |
| --- | --- |
| `guides/readme.md` | This file. Overview, layout, options. |
| `guides/upgrading.md` | Roadmap. Phased plan, decisions, versioning policy. |
| `reference/ssot.md` | Single source of truth. Where each value is authoritative. |
| `reference/changelog.md` | Version history and the fork delta. |
| `specs/now-feature-spec.yml` | Portable specification for a future Now feature. |

The folders group documents by role: `guides/` for things you read to work on
the theme, `reference/` for the authority map and history you consult, and
`specs/` for portable specifications. All markdown filenames are lowercase; CI
fails the build if any filename contains an uppercase letter.

## Development

Source lives at [github.com/menj/kolofon](https://github.com/menj/kolofon).
Issues, pull requests, and release tags are there.

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

The theme exposes one filter so far, `kolofon_seo_plugin_active`. A general
hook surface is phase 4 of the roadmap.

## Font stacks

Five options. Slug-to-label map, in radio order on the Appearance tab:

- **The reader** (`editorial`, default). Charter with a Georgia fallback, both system fonts. Loads no webfont.
- **Charter, but loud** (`xcharter`). Self-hosted extension of Bitstream Charter. Four weights.
- **Typed** (`special-elite`). Self-hosted typewriter face. One weight.
- **Office memo** (`typewriter`). Hybrid: system monospace for reading, Special Elite for headings.
- **Plaintext** (`mono`). System monospace end to end. Loads nothing.

The webfont stacks are independent: choosing Charter, but loud loads XCharter; choosing Typed loads Special Elite. Adding a stack means registering it through `kolofon_font_stacks`, optionally with a `webfont` key.

Typewriter and monospace stacks (`typed`, `office-memo`, `plaintext`) get typewriter-accurate leading — 1.9 line-height with 1rem paragraph spacing — because monospace type has taller x-height relative to em and reads crowded at proportional-serif rhythm. Post body copy is fully justified with no CSS hyphenation, since that's how a print manuscript wears its ragged word-spacing. Both details apply through the `font-<slug>` body class so future stacks that identify as monospace-family inherit them.

## Attribution

Forked from the [Chris Wiegman Theme](https://github.com/ChrisWiegman/chriswiegman-theme)
v12.7.0 by Chris Wiegman, GPL v2. The fork retains the security posture,
image-size trimming, comment removal, and head cleanup, and adds the options
page, colour system, hybrid block support, patterns, and the `Kolofon`
namespace. See `changelog.md` for the full delta.

### Third-party

- **[Parsedown](https://github.com/erusev/parsedown) 1.8.0** by Emanuil Rusev,
  MIT. Vendored under `vendor/parsedown/`, used only to render this theme's own
  documentation in the admin.

## License

GNU General Public License v2 or later. See `LICENSE.md` in the theme root,
which also records attribution and every bundled third-party component.

Bundled components keep their own licence files where they sit
(`vendor/parsedown/`, `assets/fonts/*/`, `inc/activitypub/`). Those must stay:
retaining the licence text is a condition of each grant, so removing them would
put the theme outside the terms it is distributed under.
