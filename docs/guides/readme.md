# Kolofon

Modernist, minimalist WordPress theme for the personal writer's microsite at
[menj.blog](https://menj.blog). Source: [github.com/menj/kolofon](https://github.com/menj/kolofon).

- Classic PHP templates with hybrid block-editor support (`theme.json`, block
  patterns, block styles)
- Tabbed Theme Options under **Appearance, Theme Options** with six tabs
- Paginated Blog Index rendered as a year-anchored ledger
- Mutually exclusive sections: one category per post, enforced server-side
- 38-platform social registry driving profile icons, JSON-LD `sameAs`, and a
  per-post share row, plus a separate freeform field for non-social sameAs
  pages (Gravatar, Wikipedia, Crunchbase) the icon-driven registry can't hold
- Guarded `wp_head`/`wp_footer`: a plugin fatal is caught and logged instead
  of taking the front end down
- Variable colour schemes driven entirely by CSS custom properties
- Hand-written CSS and JavaScript; the one build artefact is the minified CSS
  beside each stylesheet
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
├── page-blog.php             Blog Index page template: paginated, year-ledger layout
├── index.php                 Archive and search fallback
├── 404.php
├── searchform.php
├── archive-kolofon_status.php  Status archive, matching the blog page layout
├── screenshot.png
├── inc/
│   ├── defaults.php       Single source of truth for defaults and presets
│   ├── resilience.php     Catches plugin fatals in wp_head/wp_footer, admin notice
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
│   ├── share.php          Per-post sharing: target registry, URL builder, renderer
│   ├── email-guard.php    Email obfuscation, content filter, shortcode
│   ├── post-list.php      Shared post list renderer, previews, pagination
│   ├── sections.php       Section registry, enforcement, chooser
│   ├── tags.php           Tag rendering, section spans, topic index
│   ├── page-states.php    Planned pages, navigation badge, content notice
│   ├── meta.php           Open Graph, schema, SEO plugin detection
│   ├── docs.php           Markdown documentation rendering via Parsedown
│   ├── system-report.php  Runtime facts block, rendered on the Advanced tab
│   ├── activation.php     Auto-provisions blog index page on theme switch
│   ├── webfonts.php       Self-hosted webfont loading, per active stack
│   ├── syndication.php    RSS feed featured images, fediverse:creator meta
│   ├── migration-notice.php  Post-rename admin notice, one-shot
│   ├── dynamic-css.php    Emits :root custom properties, dark-mode block
│   ├── print-branding.php Print letterhead and MLA citation colophon
│   ├── microblog.php      Microblog and Fediverse loader, identity panel, migrations
│   ├── microblog/         Status post type, composer, timeline, REST, AP bridge
│   └── blocks.php         Pattern category and block styles
├── assets/
│   ├── .htaccess              Apache cache + compression headers for static assets
│   ├── css/
│   │   ├── main.css             Front end
│   │   ├── editor.css           Block editor
│   │   ├── admin-base.css       Shared admin tokens and components
│   │   ├── admin-options.css    Options page
│   │   ├── admin-activitypub.css Bundled engine screens
│   │   └── *.min.css            Minified builds, served in preference to the source
│   ├── js/
│   │   ├── nav-toggle.js     Collapsing navigation
│   │   ├── keyboard-nav.js   Digit shortcuts for sidebar navigation
│   │   ├── email-guard.js    Rebuilds obfuscated mailto links
│   │   ├── single-section.js Single-choice categories in the block editor
│   │   ├── hover-preview.js  Shared preview card that travels between rows
│   │   ├── search-overlay.js Full-screen search
│   │   ├── post-share.js     Copy-link button on the per-post share row
│   │   └── admin-options.js  Tab widget, colour picker, media picker
│   ├── img/
│   │   ├── profile.png       Portrait, hero fallback
│   │   ├── favicon.png       32x32
│   │   ├── icon-192.png      Android
│   │   └── apple-touch-icon.png
│   └── fonts/
│       ├── xcharter/         Bitstream Charter extended, 4 weights, opt-in via stack
│       └── special-elite/    Typewriter face, 1 weight, opt-in via stack
├── languages/
│   └── kolofon.pot          Translation template
├── patterns/
│   ├── bio-hero.php
│   ├── bio-card.php
│   ├── link-list.php
│   ├── callout.php
│   └── contact-block.php
├── vendor/
│   ├── parsedown/            Runtime dependency, MIT
│   └── activitypub/          Bundled ActivityPub 9.2.0 engine, loaded only when enabled
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
| Identity | Hero eyebrow, heading, body copy, portrait, citation name, footer text, additional profile pages (sameAs), colour scheme, font stack, lede type sizes |
| Layout | Section categories, chooser and enforcement, adjacent-post scope, chrome layout, keyboard shortcuts, content width, portrait size and style, list style and title size, hover previews, recent posts on home, Blog Index posts per page, sharing icons on posts |
| Social | Email protection mode, then one URL per platform |
| Fediverse | Fediverse address and handle, microblog, status federation, statuses on the blog, search-engine visibility, status length limit, timeline page size |
| Advanced | Content Security Policy, file editor lock, meta output, planned-page labels, export, import, restore defaults |
| Documentation | Renders the guides in `docs/` |

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

Configure the Layout tab with your category slugs in display order,
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

### Blog Index

A page using the **Blog Index** page template lists every post, grouped by
year, as a ledger: each year is a numeral in a left rail — sticky on wide
viewports, so it stays in step with its posts — and that year's posts sit to
the right as a numbered index. It is deliberately a different treatment from
the Main Index's "Recent Posts" rows, so the two pages don't read as
interchangeable.

The template paginates on its own setting, **Blog page posts per page**
(Layout tab, default 20, range 5 to 100). A static Page paginates on the
`page` query var rather than `paged`, so the prev/next links are built
directly from `get_pagenum_link()` instead of the shared `render_pagination()`
helper, which reads the global main query and would print plausible-looking
links pointing at the wrong page.

The posts the ledger renders and the `ItemList` in the page's JSON-LD both
come from `blog_index_query_args()` in `inc/post-list.php`, so the structured
data always describes exactly what is on the page. Canonical and `og:url` on
page 2 and beyond point at that page, not back at page 1.

An out-of-range page number (`/blog/page/99/` on a three-page archive) renders
the ordinary "No posts yet." empty state rather than a 404 — a deliberate
simplification, since that should only ever be a hand-typed URL.

### Social platforms and sameAs

The Social tab holds one URL field per platform in `get_social_platforms()`
(`inc/social.php`) — 38 platforms plus email and RSS. Every field left blank
is simply hidden; nothing renders and nothing is claimed. Every field filled
in does three things at once, since all three read the same registry:

- Adds an icon to the "Find me on:" row under the hero.
- Adds the URL to the `sameAs` array in the Person JSON-LD (`inc/meta.php`),
  telling search engines and AI crawlers these accounts belong to the same
  person as the site. Email and RSS are excluded from `sameAs`, since neither
  is a profile a search engine would want to link as "the same entity."
- Becomes a candidate for the per-post share row below, if that platform is
  also in `get_share_targets()`.

Adding a platform means one entry in `get_social_platforms()` with a `label`
and a single-fill `svg` path (24×24 viewBox by default; set a `viewbox` key
for anything else, as Scribd's Font Awesome mark does at 384×512) and one
matching default in `inc/defaults.php`. The field, its sanitiser, and its
`sameAs` eligibility all follow from the registry with no further edits —
`kolofon_social_platforms` is the filter to reach for instead.

Two of the registry's icons — LinkedIn and Scribd — are Font Awesome Free
marks (CC BY 4.0) rather than the public-domain Simple Icons the rest use.
`uses_attributed_icon()` checks whether either is actively filled in and, if
so, the footer prints a small "Icons by Font Awesome" credit. An icon sitting
unused in the registry carries no such obligation.

**Additional profile pages (sameAs).** Identity tab, separate from the Social
tab on purpose. Not every page that describes the site owner is a social
platform — a Gravatar profile, a Wikipedia page, a Crunchbase or IMDb entry,
a personal wiki — and none of those belong in `get_social_platforms()`, since
that registry also drives an icon and a link in the "Find me on:" row, which
none of these should have. One URL per line in this field feeds straight into
the same `sameAs` array the Social tab populates (`get_same_as_urls()` and
`parse_same_as_urls()` in `inc/meta.php`), merged and deduplicated with the
social URLs, but renders nowhere on the front end. The parser splits on
newlines only, never commas, since a URL's query string can legitimately
contain one — the same list-splitting `parse_section_slugs()` uses would
silently corrupt a URL like `...?query=a,b,c`.

### Post sharing

Layout tab, **Show sharing icons on posts** (on by default). When enabled, a
row of share links appears at the foot of every post — not Pages, not
microblog statuses — built by `render_post_share()` in `inc/share.php`.

This is a separate, smaller registry from the profile one above:
`get_share_targets()` lists eight platforms with a stable, unauthenticated
share-intent URL (X, Facebook, LinkedIn, Bluesky, Reddit, Telegram, WhatsApp,
email), plus a copy-link button that needs no network target at all. A share
target only renders if its slug also has an icon in `get_social_platforms()`,
so the two registries stay in sync without one importing the other; extend
sharing through `kolofon_share_targets` and, for a platform this file doesn't
already know how to build a URL for, `kolofon_share_url`.

The row shares its circular icon-button styling with the profile row via the
same `.hero-social-link` class, so the two read as one visual family even
though they point at different things — the profile row links to the site
owner's accounts, the share row shares the post being read, and carries no
personal data. The copy-link button is the one part that needs JavaScript
(`assets/js/post-share.js`, loaded only when the row can appear); the
platform links are plain anchors and work with no script at all.

### Resilience

WordPress runs `wp_head` and `wp_footer` as one flat list of every active
plugin's callbacks, with no isolation between them. If any single callback
throws, the request dies mid-render and the visitor gets WordPress's recovery
text under whatever half of the page had already streamed.

Kolofon calls both hooks through `\Kolofon\run_guarded_hook()`
(`inc/resilience.php`), which wraps `do_action()` in a `try`/`catch` for
`\Throwable`. Since PHP 7 a missing-file fatal throws an `\Error`, which is
genuinely catchable. When one is caught, the real error goes to the PHP error
log with hook, message, file and line; the page finishes rendering for every
visitor; and a small notice appears in the footer for logged-in administrators
only.

That notice leads with plain language rather than a stack trace —
`classify_guarded_hook_error()` pattern-matches the exception to name the
actual failure ("a missing file", "a missing function (outdated plugin)", "a
plugin conflict", a memory limit, a timeout), falling back to the exception
class for anything it doesn't recognise. The full technical detail sits behind
a native `<details>` toggle that needs no JavaScript.

This is a safety net, not a fix: the underlying plugin bug still needs
fixing. It exists so that one broken third-party hook degrades to "the footer
scripts didn't run" instead of "the site is down."

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

CSS and JavaScript are hand-written; there is no bundler, transpiler, or
preprocessor, so a clone runs immediately. The one build artefact is the
minified CSS: each `assets/css/*.css` has a `*.min.css` beside it, and
`inc/enqueue.php` serves the minified file in preference unless `SCRIPT_DEBUG`
is on, in which case it serves the readable source. Edit the source, then
regenerate its `.min.css`.

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
field registration, then the sanitiser. All three are required — hard
invariant 2 in `reference/ssot.md` — because an option registered without a
sanitiser silently falls back to `sanitize_text_field`, which quietly discards
any range or type constraint the field declared.

Adding a social platform means one entry in `get_social_platforms()` and one
matching default; the field loop, sanitiser, and renderer pick it up with no
further changes.

The theme exposes fifteen filters:

| Filter | Purpose |
| --- | --- |
| `kolofon_defaults` | The default option values |
| `kolofon_sanitized_options` | The option array after sanitising |
| `kolofon_option_fields` | The registered field definitions |
| `kolofon_option_tabs` | The options page tabs |
| `kolofon_option_sanitizers` | The sanitiser registry |
| `kolofon_colour_presets` | Available colour schemes |
| `kolofon_font_stacks` | Available font stacks and their webfonts |
| `kolofon_portrait_styles` | Hero portrait shape options |
| `kolofon_root_css` | The emitted `:root` custom-property block |
| `kolofon_social_platforms` | The social platform registry |
| `kolofon_share_targets` | The per-post sharing targets |
| `kolofon_share_url` | The share URL built for one target; also how to add a scheme for a target this file doesn't already know how to build one for |
| `kolofon_system_report` | Rows in the runtime facts block |
| `kolofon_csp_directives` | Content Security Policy directives |
| `kolofon_seo_plugin_active` | Whether an SEO plugin owns metadata |

A palette added at runtime through `kolofon_colour_presets` is checked for
WCAG AA contrast under `WP_DEBUG`: if text, muted, or accent falls below 4.5:1
against its own background, `_doing_it_wrong()` names the scheme and the ratio.
The check is inert in production and skips non-hex colours rather than guessing
at them.

## Font stacks

Five options. Slug-to-label map, in radio order on the Identity tab:

- **The reader** (`editorial`, default). Charter with a Georgia fallback, both system fonts. Loads no webfont.
- **Charter, but loud** (`xcharter`). Self-hosted extension of Bitstream Charter. Four weights.
- **Typed** (`special-elite`). Self-hosted typewriter face. One weight.
- **Office memo** (`typewriter`). Hybrid: system monospace for reading, Special Elite for headings.
- **Plaintext** (`mono`). System monospace end to end. Loads nothing.

The webfont stacks are independent: choosing Charter, but loud loads XCharter; choosing Typed loads Special Elite. Adding a stack means registering it through `kolofon_font_stacks`, optionally with a `webfont` key.

Typewriter and monospace stacks (`special-elite`, `typewriter`, `mono`) get
typewriter-accurate leading — 1.9 line-height with 1rem paragraph spacing —
because monospace type has taller x-height relative to em and reads crowded at
proportional-serif rhythm. Post body copy is fully justified with no CSS
hyphenation, since that's how a print manuscript wears its ragged
word-spacing. Both details apply through the `font-<slug>` body class so
future stacks that identify as monospace-family inherit them.

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
- **[ActivityPub](https://github.com/Automattic/wordpress-activitypub) 9.2.0**
  by Matthias Pfefferle & Automattic, MIT. Vendored under
  `vendor/activitypub/`, providing the federation engine behind the Fediverse
  tab. No engine files are modified; the four path constants derive from the
  theme directory. See `LICENSE.md` for the full statement.

## License

GNU General Public License v2 or later. See `LICENSE.md` in the theme root,
which also records attribution and every bundled third-party component.

Bundled components keep their own licence files where they sit
(`vendor/parsedown/`, `assets/fonts/*/`, `vendor/activitypub/`). Those must stay:
retaining the licence text is a condition of each grant, so removing them would
put the theme outside the terms it is distributed under.
