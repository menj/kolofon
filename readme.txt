=== Kolofon ===

Contributors: menj
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 5.7.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, one-column, custom-colors, custom-menu, editor-style, featured-images, translation-ready

Modernist, minimalist WordPress theme for a writer's microsite, with tabbed theme options and variable colour schemes.

== Description ==

Kolofon is a modernist, minimalist theme for a single author's microsite. The name comes from the Malay spelling of "colophon," the printer's mark at the end of a manuscript that records who set the type and on what tools. A writer's microsite is the same idea in web form.

The theme uses classic PHP templates with hybrid block-editor and theme.json support. It is built around a single control surface: everything configurable lives under Appearance in a tabbed Theme Options screen, rather than being scattered across the Customizer and the Site Editor.

Features:

* Two built-in colour schemes, Charcoal and Ivory, with variable colours driven by CSS custom properties, so a custom scheme recolours the whole theme including its gradient accents.
* A tabbed Theme Options screen: identity, sections, social, appearance, layout, advanced, system, and documentation.
* Five editorial font stacks, with a live typography preview.
* A full-screen search overlay, opened from a header icon or Ctrl/Cmd+K, that enhances native WordPress search and degrades gracefully without JavaScript.
* Comprehensive schema.org / JSON-LD output describing every page type (Person, WebSite, ProfilePage, BlogPosting, WebPage, AboutPage, CollectionPage, SearchResultsPage, and BreadcrumbList), for discoverability by search engines and AI tools. It stands down automatically when an SEO plugin is active.
* Branded print output: printing a post produces a document with a letterhead masthead and a citation colophon, suited to scholarly and long-form writing.
* A social profile registry covering common platforms, entered on the Social tab.
* Settings export and import, so a configuration can be moved between sites.
* XHTML-compliant, ARIA-annotated markup throughout, and a skip link to content.
* Translation-ready, with a bundled kolofon.pot.

== Installation ==

1. In your WordPress admin, go to Appearance > Themes > Add New > Upload Theme.
2. Choose the Kolofon zip file and click Install Now.
3. Click Activate.
4. Go to Appearance > Theme Options to configure the identity, colour scheme, sections, and social profiles.

If you upload by FTP instead, upload the extracted `kolofon` folder to `wp-content/themes/`, then activate the theme from Appearance > Themes. When updating an existing install by FTP, remove the old `kolofon` folder first so no superseded files are left behind; your settings live in the database and are preserved.

== Frequently Asked Questions ==

= Where do I configure the theme? =

Everything is under Appearance > Theme Options. The theme deliberately keeps its settings there as a single control surface rather than in the Customizer or the Site Editor.

= How do I change the colours? =

Theme Options > Appearance offers the Charcoal and Ivory schemes. Colours are driven by CSS custom properties, so the accents, gradients, and headings all recolour together.

= Does it work with SEO plugins? =

Yes. The theme emits its own meta tags and JSON-LD, but stands down automatically when a known SEO plugin (Yoast, Rank Math, All in One SEO, SEOPress, Slim SEO, or The SEO Framework) is active, so tags are never emitted twice. The Advanced tab reports which plugin owns the output.

= Does the theme collect any user data? =

No. Kolofon does not collect, track, or transmit any user data. It makes no external requests except when the site owner explicitly configures a feature that fetches a remote resource.

= Is the theme translation-ready? =

Yes. All strings use the `kolofon` text domain, and a `languages/kolofon.pot` template is included.

== Copyright ==

Kolofon WordPress Theme, (C) 2026 Mohd Elfie Nieshaem Juferi.
Kolofon is distributed under the terms of the GNU GPL version 2 or later.

This theme is a fork of the Chris Wiegman Theme, version 12.7.0, which is licensed under the GNU GPL version 2 or later. See docs/reference/changelog.md for the fork history and full attribution.

This theme bundles the following third-party resources:

Parsedown, (C) Emanuil Rusev, MIT License.
Source: https://github.com/erusev/parsedown

XCharter font, (C) Michael Sharpe, SIL Open Font License 1.1.
Special Elite font, (C) Astigmatic, Apache License 2.0.

Font licenses are included alongside the fonts in `assets/fonts/`.

== Changelog ==

For the complete, detailed version history, see docs/reference/changelog.md in the theme folder.

= 5.7.0 =
* Added a design and standards audit that detects discarded CSS, conflicting rules, crowding and undersized controls automatically. Toggle hit target raised to meet WCAG 2.2.

= 5.6.1 =
* Fixed field labels sitting hard against the panel's gold edge: a collapsed table ignores its own padding, so the inset now lives on the cells.

= 5.6.0 =
* Theme Options redesigned: branded masthead, gold-edged panel, and help text moved beside its label so the form reads as two clean columns.

= 5.5.0 =
* Standards audit: 25 XHTML attribute violations fixed, one WCAG contrast failure corrected. CSS validates clean and both schemes pass WCAG 2.2 AA with zero violations.

= 5.4.0 =
* Post tags restyled: framed by a "Tagged" label above a hairline, set as accent-tinted rounded pills matching the section chooser.

= 5.3.0 =
* The Theme Options form now carries the theme's identity: labels, fields, help text and tabs restyled away from stock wp-admin.

= 5.2.0 =
* Post list titles are larger, and a new Layout setting controls their size.

= 5.1.2 =
* Preview card titles now scale with the card width, so they read correctly at any hover preview size.

= 5.1.1 =
* Preview card titles are larger and bolder, restoring the prominence they lost when they moved to the foot of the card.

= 5.1.0 =
* Posts without a featured image now get a generated printer's-mark device in the hover preview, seeded from the post slug and drawn in the active scheme's colours.

= 5.0.0 =
* Removed the tags-in-post-lists display entirely, along with its two options. Tags still appear on single posts and tag archives still work.

= 4.10.0 =
* Tags in post lists are now off by default, restoring the clean list. The Sections tab toggle turns them back on.

= 4.9.5 =
* Lede links: hero body links now render bold in the text colour with a scheme-derived accent underline; help text documents that links are allowed.

= 4.9.4 =
* Documentation: corrected the Parsedown status note; upstream revived and the bundled copy is the current 1.8.0 stable (February 2026).

= 4.9.3 =
* LICENSE corrected: Kolofon name, current doc path, the v2-or-later grant matching the declared license, and the full GPLv2 text now included.

= 4.9.2 =
* Documentation reorganised into docs/guides, docs/reference, and docs/specs subfolders; the in-admin Documentation tab and CI updated to match.

= 4.9.1 =
* Documentation: the Now feature spec now includes Kolofon's colour, token, and layout design details for a faithful rebuild.

= 4.9.0 =
* Removed the listing-header gradient wash; listing pages now use a clean flat header with the accent heading. The home hero keeps its gradient.

= 4.8.2 =
* Fixed the listing-header gradient rendering as a boxed panel inset from the page edge; it now spans full width like the hero.

= 4.8.1 =
* Search results heading: the query term now uses the text colour to stand apart from the accent label.

= 4.8.0 =
* Print citation now follows MLA 9th edition; added a Citation name field for the inverted author name.

= 4.7.0 =
* Added readme.txt in WordPress.org format; corrected theme tags to the approved, verified set.

= 4.6.0 =
* Added branded print output: a letterhead masthead and citation colophon when a post is printed.

= 4.5.0 =
* Consistent header treatment across every page type: listing pages share the hero's gradient language while reading pages stay clean.

= 4.4.3 =
* The search overlay form now carries the theme's character on both colour schemes.

= 4.4.1 =
* Fixed a bug where the closed search overlay could intercept clicks across the site.

= 4.4.0 =
* Full gradient treatment on the Theme Options page.

= 4.2.0 =
* Added a full-screen search overlay opened from the header.

= 4.1.0 =
* Comprehensive schema.org coverage across every page type.

= 4.0.0 =
* Removed the Now page feature to focus on core features. See docs/reference/changelog.md.

= 1.0.0 =
* Initial release as Kolofon, forked and rebranded from the Chris Wiegman Theme 12.7.0.
