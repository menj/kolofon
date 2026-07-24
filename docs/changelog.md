# Changelog

## 3.0.1

### Added
- Migration confirmation notice. When the 3.0.0 rename migration copies a legacy `menj_bio_options` row into `kolofon_options`, it now also records how many settings were migrated. On the next admin page load a dismissible success notice appears reporting the count and offering an "Open Kolofon Options" button. Gives the site owner positive confirmation the migration ran rather than trusting silence.
- New module `inc/migration-notice.php`. Notice fires only when the migration flag exists, dismisses via `admin-post.php` with a nonce (permanent removal) or the standard is-dismissible X (session only). Capability-gated to `manage_options`.

### Not covered by the notice
- A child theme's `Template:` header. If any site running Kolofon has a child theme naming `menj-bio` as its Template, the child breaks on activation and no notice about it will appear — the notice only reports what the migration handled, not what a child theme's config might need updating. Recorded in `docs/upgrading.md`.


## 3.0.0 — theme rename to Kolofon

Major bump per invariant 5: the text domain and option row key both change, which is exactly the class of breaking schema change semver requires a major bump for. Full rename, applied consistently across every layer.

### Renamed

- Theme name: `menj.bio` → **Kolofon** (Malay spelling of "colophon" — the printer's mark at the end of a manuscript recording who set the type).
- Directory: `menj-bio/` → `kolofon/`.
- Text domain: `menj-bio` → `kolofon`. All 372 user-facing gettext strings retranslated in the .pot file.
- PHP namespace: `MENJ\Bio` → `Kolofon`. All 26 namespace declarations and 87 fully-qualified references updated.
- PHP constants: `MENJ_BIO_VERSION`, `MENJ_BIO_URI`, `MENJ_BIO_DIR`, `MENJ_BIO_OPTION_KEY` → `KOLOFON_*`.
- Filter/action prefixes: `menj_bio_*` → `kolofon_*`. Breaks any child-theme code that hooked the theme's filters.
- Option row key: `menj_bio_options` → `kolofon_options`. Migrated (see below).
- CSS variables: `--mb-*` → `--k-*`. 152 references.
- CSS classes: `.menj-bio-*` → `.kolofon-*`, `.mb-email` → `.k-email`, `.mb-font-*` → `.k-font-*`. 132 rules touched.
- Translation file: `languages/menj-bio.pot` → `languages/kolofon.pot`.
- Test suite comments and Composer package name.

### Migrated

A one-shot migration runs on `after_setup_theme` at priority 20 (the same hook that handles other stored-option migrations):

1. Reads the legacy `menj_bio_options` row.
2. If it exists AND `kolofon_options` does not, copies the payload verbatim.
3. Deletes the legacy row.
4. Same rule applies to `menj_bio_typewriter_reset` (the migration flag from 2.5.0).

Idempotent: on subsequent loads the legacy rows are gone, so the migration is a no-op. No user-facing action required — a site upgrading from 2.10.x to 3.0.0 keeps every setting intact.

### Not renamed

- Author: `Mohd Elfie Nieshaem Juferi` (still MENJ, still your name).
- GitHub username: `menj` in URLs. The *repo* moves from `menj/menj-bio` to `menj/kolofon`.
- Domain: `menj.bio` stays the site URL. Kolofon is the theme running on it.

### Why the name

A colophon is the printer's mark at the end of a manuscript — historically containing the scribe's identity, the tools used, sometimes the date and location of setting. A bio microsite is exactly the same idea in web form: a small object recording who you are, what tools you used, when you set the type. The Malay spelling ties the name to your linguistic identity without foregrounding it.

### Sceptical note

Renaming a theme in-place is unusual and creates one durable trap: search engines and RSS readers may have cached "menj.bio Theme" as the name. If Kolofon shows up anywhere with a mixed identity, it's a caching artefact, not a bug in the theme itself.


## 2.10.2

### Docs
- **Root-level `README.md`** added. GitHub renders it on the repository landing page at [github.com/menj/kolofon](https://github.com/menj/kolofon), where the four docs inside `docs/` are not visible as a landing. Points inward to `docs/` for full documentation. Follows GitHub capitalisation convention rather than the theme's `docs/` lowercase convention, since invariant #7 in `ssot.md` specifically scopes lowercase to the four canonical files inside `docs/`.
- **Repository named in `docs/readme.md`.** Opening line now cites [github.com/menj/kolofon](https://github.com/menj/kolofon) alongside the site, and the Development section explicitly points at the source repo.
- **Repository and release archive locations recorded in `docs/ssot.md`** file-of-record map. Names the release workflow's version-tag constraint alongside.


## 2.10.1

### Docs
- Hover previews description in `readme.md` rewritten to cover the 2.10.0 typographic peek: both photographic and typographic tiles now share the same anchor.
- `ssot.md` corrected: `preview_size` default is 140 (not 240), clamp range is 100–240 (not 140–400), `list_style` accepts `index` alongside `stacked` and `columns`. Hover preview content now named in the authority map.
- `upgrading.md` — the 1.0.0 "hover-revealed illustration cards" reasoning extended with a 2.10.0 footnote: the "no preview for image-less posts" rule was itself a subtle failure and got corrected.


## 2.10.0

### Added
- **Typographic hover previews for posts without a featured image.** Same 3:2 anchor and same slot in the list layout as the photographic version, but the peek renders the post title (up to 10 words, three-line clamp) in the theme's heading font over a subtle palette-derived background. Image-less posts now get a peek of their own rather than being second-class rows.

### Changed
- `post_list_classes()` simplified: the `has-previews` class now attaches whenever previews are enabled, since every row emits a preview (photographic or typographic). Previously it inspected the query for at-least-one-thumbnail. The `$query` parameter stays in the signature for backward compatibility but is no longer consulted.

### Notes
- The design intent, in one line: a bio microsite that publishes intermittently will often have image-less posts, and treating them as "the hover feature doesn't apply to you" reads as second-class. Turning the placeholder into an editorial affordance is closer to the spirit of the theme than either always-empty gutters or per-row jitter.


## 2.9.9

### Roadmap
- Phase 7.4 (GitHub release updater) declined. Manual zip upload from the GitHub release page is the chosen workflow. Neither vendoring `plugin-update-checker` (~200 KB) nor requiring Composer earns its cost for a single-user microsite updated by hand. `docs/upgrading.md` updated to record the decision and its reasoning; nothing about the theme needs to change to add it later, since an update checker is a standalone concern.
- Construction is complete. Two items remain, both environmental verification rather than code: PHPCS findings (1.3) and the core-block dequeue check (7.2). Both need a running WordPress instance to run against.

### Docs
- `ssot.md` gained two hard invariants from this session's mistakes: containing-block audits (the 2.9.4–2.9.6 hover-preview saga) and activation-hook idempotency (the 2.6.0 blog-page auto-provisioning).
- Directory listing in `readme.md` updated with `activation.php`, `webfonts.php`, `syndication.php`, `assets/fonts/`, and `tests/`.
- Hover preview description in `readme.md` rewritten to match the 2.9.6 behaviour.


## 2.9.8

### Changed
- Columns list style now shows the full day-month-year on every row as `j M Y` — "24 JUL 2026". Uniform format across every row regardless of year. Retires the 2.9.7 conditional-year behaviour in favour of always-visible dates. Column width stays at 6.5rem, still fits the format cleanly. Uppercase transform on the column handles JUL/DEC.


## 2.9.7

### Fixed
- Columns list style hid the year on every date, which was defensible when the whole list was from the current year but silently ambiguous when it spanned years — a 2024 post and a 2026 post rendered identically. Now the year is shown only when the post's year differs from the current year: current-year posts stay compact as "Jul 24", older posts render as "Dec 4, 2024". Self-heals across the year boundary — a post from 2026 will automatically start showing as "Jul 24, 2026" once 2027 arrives.
- Date column widened from 3.6rem to 6.5rem to accommodate the longer format when it appears. `white-space: nowrap` added as a safeguard against the year wrapping if font metrics vary.


## 2.9.6

### Fixed
- Hover preview landing in the wrong place, overlapping row text instead of the reserved gutter. Root cause: `.post-item a` had `position: relative` set for an older design, which made the anchor the preview's containing block. `right: 0` on the preview then measured against the row's right edge (the content area) rather than the list's right edge (which includes the gutter). The whole gutter mechanic was defeated because the preview never learned about the padding-right expansion at all.
- Removed `position: relative` from `.post-item a`. Nothing else depended on it — checked every rule in the stylesheet before removing. The preview now positions against `.post-list.has-previews`, which has `position: relative`, and lands in the gutter as designed.

### On 2.9.4 through 2.9.6
- Three versions in a row on the same hover preview. The visualiser turn showed me correct behaviour in an abstraction, and I shipped code assuming the abstraction matched the real DOM. It did not, because a legacy `position: relative` on the row anchor was breaking the containing-block chain in ways only the actual browser could reveal. The lesson recorded for the trail: when the visualiser and the shipped result diverge, the DOM is the source of truth. Look at what the real element positions against, not what you meant it to position against.


## 2.9.5

### Fixed
- Login screen notice boxes ("You are now logged out," authentication errors, password reset confirmations) inherited WordPress default styling: white background, pale grey text, thin left border. On the Charcoal palette this rendered as a bright block sitting incongruously on the dark surface. Now the notice adopts the site palette: `$bg` background, `$text` colour, `$rule` border, accent-coloured left stripe. `#login_error` covered alongside `.message` and `.notice`, since WordPress uses that separate ID for authentication failures.

### Note on the trade
- Error notices and success notices now share the accent-coloured stripe rather than red-vs-green. Menj-bio uses one accent per palette and doesn't have a distinct danger colour; introducing one would break the palette discipline. The wording of the notice carries the semantic — "logged out" versus "password incorrect" — which is enough for a login screen used by the site owner.


## 2.9.4

### Changed
- Hover preview anchored to the top of the list rather than to the row being hovered. Moving between rows now swaps the preview image but the peek stays put at a fixed position — the reader always knows where to look. 2.9.3 placed the preview beside whichever row was hovered, which meant the peek jumped vertically as the cursor moved down. 2.9.4's anchor is `top: 0, right: 0` on the list itself, so the peek's spot in space is a property of the list, not of any row.
- Row's own `position: relative` removed. Previews now position against the list container, resolving through the ancestor chain automatically since only the list has `position: relative` in the new setup.


## 2.9.3

### Changed
- Hover preview gutter is now on-demand rather than absent. 2.9.1 stripped the always-reserved gutter and let the preview overlay content; 2.9.3 brings the gutter back but only while the list is hovered. Zero reserved space by default, `padding-right` transitions to `preview_size + 1.5rem` when any row in a previewed list is hovered, and back to zero when the mouse leaves. Preview fades in as usual, still at 3:2 aspect from 2.9.2.
- Preview repositioned back to vertically-centred beside the row, since the list is now making room horizontally. The 2.9.1 "below-right" placement is retired; it was the right answer for "no reserved space, preview overlaps content," but now that the list makes room, floating beside the row reads more naturally.

### Notes on the design
- The gutter opens at the list level (`.post-list.has-previews:hover`), not the row level. This is the load-bearing choice that prevents the "cursor chases layout" failure mode I flagged in the discussion of 2.9.2: if each row controlled its own gutter, moving between rows would open and close it repeatedly, and the row your mouse is about to enter would shift out from under you. At the list level the gutter is either open (mouse is anywhere in the list) or closed (mouse isn't), and moving between rows changes nothing.
- `prefers-reduced-motion: reduce` is honoured. The gutter still opens and the preview still appears, just without the transition. The interaction is preserved; the animation is removed.


## 2.9.2

### Fixed
- Hover preview enforced to a consistent 3:2 landscape aspect ratio. Previously the preview inherited its source image's aspect, so portrait sources (typewriter, phone screenshots) rendered as tall columns while landscape sources (cat, editorial photos) rendered as compact cards. Result: same width, wildly different heights, list read as untidy. Now: `aspect-ratio: 3 / 2` on the preview container plus `object-fit: cover` on the image, so every preview renders at 140 × 93 px regardless of source. Cropping is centred, which is where most editorial thumbnails place their subject.


## 2.9.1

### Changed
- Hover preview redesigned as a small floating peek anchored below-right of the hovered row. The previous design reserved a gutter equal to the preview width plus 2.5rem to prevent shift, then floated a 240px preview centred on the row, which on a bio microsite with sparse content pushed the preview above adjacent section headings and off the right edge of the container. New behaviour: no reserved gutter, no card lift on the row, preview appears below-right of the row, small enough to read as a peek rather than a card.
- Default `preview_size` narrowed from 240 to 140 to suit the floating peek scale.
- `preview_size` clamp range narrowed from 140–400 to 100–240. Values above 240 are clamped down on read rather than reset; a stored 350 becomes 240 rather than falling back to the default. Not a full reset but worth naming, since invariant 5 (removing or renaming keys requires a migration) is adjacent to this — narrowing a range is a softer form of the same class of change.

### Removed
- Row card-lift on hover. It suited the reference layout it came from, less so a compact bio site.
- `.post-list.has-previews { padding-right: … }` gutter reservation. No longer needed.


## 2.9.0

Three patterns brought forward from Chris Wiegman's cwplugin (2.4.0), after auditing what applies to a bio microsite versus what is specific to his hosting.

### Added
- **Featured images in RSS feeds.** New module `inc/syndication.php` emits an `<enclosure>` tag on each RSS item and prepends the featured image inline to the feed content. Feed readers that render item bodies (most of them) now show the image without a click through, and spec-compliant readers see the enclosure too.
- **fediverse:creator meta tag** on singular views when a Mastodon URL is configured. Derives the handle from the URL rather than asking for a separate setting: `https://mastodon.social/@user` becomes `@user@mastodon.social`. Emits on singulars only, since home and archives have no single creator to attribute. Uses the existing `social_mastodon` option key, so no schema change.
- `Permissions-Policy` security header, closing the browser API surface a bio microsite has no legitimate reason to touch (geolocation, camera, microphone, payment, and so on). Fullscreen remains permitted from same-origin for image lightboxes and video posts.

### Not adopted from cwplugin
- Jetpack AI disable and WordPress.com RUM script removal. Both are hosting-specific for chriswiegman.com and irrelevant on self-hosted WordPress. Some sites might genuinely want Jetpack AI.
- `X-XSS-Protection: 1; mode=block`. Modern browsers ignore it and OWASP recommends not setting it, since the legacy XSS auditor it targeted introduced its own vulnerabilities and was removed from Chrome in 2019. Menj-bio's omission is deliberate and stays.

### Notes on version handling
- Fediverse handle derivation verified against seven URL shapes including edge cases (Twitter profiles, deep paths, malformed input) before shipping.


## 2.8.3

### Changed
- Post body copy is now fully justified. Scoped to `.e-content` paragraphs and list items only — headings, figures, blockquotes, hero copy, and dek excerpts keep their own alignment.
- No CSS hyphenation. Rivers of word-spacing will appear on narrow lines, particularly on monospaced stacks, and are the deliberate honest cost of full justification. A print manuscript wears the same ragged word-spacing rather than mangling words at the syllable; the theme follows the same convention.
- Applied at every breakpoint. Phone-width columns will show wider rivers than desktop; kept consistent because the alternative would be a mixed alignment across screen sizes.


## 2.8.2

### Changed
- Body copy line-height raised to **1.9** on stacks using monospace or typewriter faces (Typed, Office memo, Plaintext). Matches how real typewritten documents sat on the platen: 1.5x to 2x character height per line-feed, plus compensation for monospace's taller x-height relative to em. Proportional stacks (Editorial serif, XCharter) keep the default 1.65 — that rhythm is calibrated for serif and works there.
- Paragraph bottom margin tightened to 1rem on those same stacks, since at 1.9 leading the previous 1.2rem started reading as a section break rather than a paragraph break.
- Post-list excerpt (index style) rhythm also raised to 1.85, so a row on the index list reads as one document rather than a title with mismatched dek underneath.

Headings deliberately unchanged. A typed chapter heading was always one tight line even in double-spaced manuscripts; the rhythm rule is about running text, not display.

Applied through the `font-<slug>` body class established in 1.5.0, so future stacks that opt in by declaring themselves monospace-family get the rhythm automatically.


## 2.8.1

### Fixed
- Thin vertical line down the right edge of the bundled profile portrait. Diagnosed as a fully-opaque last column baked into the PNG at export time (column 359 had 360 opaque pixels while columns 340-358 tapered naturally from 74 down to 62). Trimmed to 359×360. Invisible on masked variants (circle, rounded, square) because `object-fit: cover` crops to square regardless; imperceptible on the floating variant at rendered size.


## 2.8.0

Four things brought forward from the parent theme (Chris Wiegman's chriswiegman-theme 12.10.2), after actually reading it for the first time in a long time.

### Added
- **Playwright smoke tests** at `tests/e2e/`. Config adapted from the parent theme (12.9.7). Four tests: home page renders, hero heading is visible, Recent Posts section is present, `/blog` resolves without a 4xx, no PHP errors appear in output. Reporter set to list plus HTML plus GitHub. Closes phase 7.3 on the roadmap — properly scoped to "smoke tests for a small theme," not the PHPUnit setup I had been overthinking.
- **Section filter on the blog index page.** Adapted from the parent theme's category-filter row shipped in 12.10.0. Menj-bio's blog index now shows a post count line and a row of section pills above the year-grouped list, so a reader landing at `/blog` can jump into a section instead of scrolling the full chronology. Uses `get_sections()` (already established in Phase 2) rather than raw `get_categories()`.
- One missing dequeue: `rest_output_rsd` on `xmlrpc_rsd_apis`. Small, defensible, in the parent theme.

### Changed
- **Search form label hidden with `.screen-reader-text` positioning** rather than the visible label with dedicated styling that 2.7.1 added. Rethink prompted by seeing the parent theme use `display: none` on the label — that's the a11y-wrong version of the same instinct, but the instinct itself is correct. Menj-bio's approach positions the label off-screen so it stays in the accessibility tree. The 2.7.1 layout for `.search-form` is preserved.

### On not adopting
- Post-column customisation for CPTs (parent theme's `includes/post_columns/`): the parent has speaking-engagement post types kolofon doesn't have. Not applicable.
- SCSS build pipeline: kolofon hand-writes CSS by policy.
- The parent theme's SCSS `page-index.scss` component. Menj-bio has the equivalent styling now (shipped in 2.7.1), though I only realised on this reading that 2.7.1 was reconstructing something that already existed in the parent — the fix was correct, the framing "the class had no rules" was wrong. Recorded for the next reviewer.


## 2.7.2

### Changed
- Font stack labels renamed for register. Editorial serif → The reader, XCharter → Charter, but loud, Special Elite → Typed, Monospace body Special Elite headings → Office memo, Monospace → Plaintext. Slugs unchanged (`editorial`, `xcharter`, `special-elite`, `typewriter`, `mono`), so nothing else needed touching. Labels-only; no migration; no code paths altered.


## 2.7.1

### Fixed
- **404 page and blog index template rendered unstyled.** Both templates use `.page-index` as their root article class and expected the same rhythm as a singular post, but `.page-index` and `.description` were selectors with no rules. On any font stack other than the site defaults the divergence was obvious; the visible failure was Special Elite headings sitting at browser-default line-height while the rest of the site sat at the theme's rhythm. Added proper styling for `.page-index .content`, `.description`, `.year-heading`, and the native WordPress search form so its submit button no longer falls out of the site's font.
- **Blog index diagnostic on the System tab.** Reports the resolved URL, the fallback source used, and whether a Posts page or Blog-Index-template page exists. Turns "why is /blog a 404" from a mystery into a visible answer.

### Notes on what wasn't a bug
- The wordmark alone with no navigation is expected on a site with no menu assigned to the Primary location. WordPress hides the nav when `has_nav_menu( 'primary' )` returns false; assigning a menu at Appearance > Menus fixes it.


## 2.7.0

Two layout patterns adopted from the Book WP reference theme, after reviewing
that codebase for what would translate to a bio microsite rather than what
would look striking in a screenshot.

### Added
- **Index post list style.** A third option under Layout, joining Stacked and Columns. A hairline-ruled row list where the title carries the eye, the year sits right-aligned as a small annotation, and the excerpt wraps onto its own line. Hover shifts the whole row rightward by a rem. Suits sites organised by title rather than by date. Adapted from Book WP's TOC pattern.
- **Section eyebrow on single posts.** Small uppercase section name above the post title, with the section description underneath if one is set. Reinforces which section the reader is in on a post arrived at from search or a share, where the section chooser is nowhere in sight.
- `get_primary_section()` in `inc/sections.php` — the read-time counterpart to `enforce_single_section()`. Same deterministic rule (configured order wins, otherwise first assigned) so posts predating enforcement resolve to a stable answer without needing a migration.

### Changed
- `post_list_item()` refactored: the two-way boolean on Columns is now a proper three-way switch. The style-specific date format (site format, `M j`, or year alone) sits with the branch that needs it, rather than in a coupled ternary.

### Not adopted from Book WP
- Time-of-day tinting, per-form typography, reading analytics, bilingual switching. Recorded as decisions in the last review turn: none of them serve the bio-microsite brief, and taking them would be cargo-culting.


## 2.6.0

### Added
- **Blog index page auto-provisioning.** A page at `/blog` carrying the Blog Index template is created on `after_switch_theme` if none exists. Fresh installs no longer require the site owner to know they need to create the page and assign the template manually.
- New module `inc/activation.php`, the theme's first activation hook.

### Behaviour details
- **Idempotent**: existence is checked by template rather than slug, so a page renamed by the site owner is still recognised and no duplicate is created.
- **Non-intrusive**: does not touch Settings > Reading. The theme reads `page_for_posts` as its first `get_blog_index_url()` fallback and defers to it if the site owner has made a choice there, but does not set it, since that interacts with the Front page setting.
- **Respects an existing Posts page**: if `page_for_posts` is already set, the activation hook exits early without creating anything. Prevents the theme from leaving an orphaned page on a site whose owner had already made their own arrangement.
- **Recovers from deletion**: if the page is deleted, it comes back on the next activation. Site owners who deliberately want no blog page can simply not switch themes.
- **Handles slug clashes**: if `/blog` is taken by an unrelated page, WordPress appends `-2` on insert and the collision is written to `error_log` so it surfaces rather than confuses.


## 2.5.1

### Fixed
- "View all" on the home page pointed at `/blog` through a coincidental fallback. `get_post_type_archive_link( 'post' )` returns false, since WordPress does not register an archive for the built-in `post` type, so `home_url( '/blog' )` always fired. That worked on menj.bio because a page exists at that slug, but silently 404s anywhere else.
- New helper `get_blog_index_url()` in `inc/sections.php` resolves the blog listing through three fallbacks: the "Posts page" set under Settings > Reading, then a page carrying the Blog Index template, then `/blog` as convention. The section chooser now shares this helper, so the site-wide "all sections" link and the "View all" link on the home page agree on where the blog lives.


## 2.5.0

### Added
- **Editorial serif restored as the default.** Charter body and headings, with a Georgia fallback. Loads no webfont.
- **Monospace body, Special Elite headings** stack under the `typewriter` slug. The typewritten face carries the section headings; the body reads in clean system monospace. Loads Special Elite only, not XCharter.

### Changed
- Font stack list is now five: Editorial serif (default), XCharter, Special Elite, the new typewriter hybrid, and Monospace.
- `migrate_stored_options()` extended with a one-shot flag for the revived `typewriter` slug. Anyone on the 2.2 or 2.3 meaning of typewriter (Charter over Courier) is reset once to Editorial serif on upgrade. Anyone on 2.5 selecting the new typewriter keeps it, since the reset only fires when the flag has not yet been set. Verified across upgrade, opt-in, and unrelated-stack scenarios.

### Note on my mistakes
- 2.4.0 removed Editorial serif in error. I misread "use these" as a replacement instruction. Editorial had been explicitly set as the default a version earlier, and I should have kept it. Restored here and the migration flow above accounts for anyone caught in the middle.


## 2.4.0

### Added
- **Webfont loading**, self-hosted, opt-in per stack. `inc/webfonts.php` preloads the primary weight, attaches `@font-face` inline on the main stylesheet with `font-display: swap`, and does both only when the active stack asks for it. A stack declares its files under a `webfont` key; nothing loads for stacks that do not.
- **XCharter** as its own stack (default). Roman, italic, bold, bold italic. Ships under `assets/fonts/xcharter/` under the extended Bitstream Charter licence.
- **Special Elite** as its own stack. A digital revival of a mid-century typewriter face. Ships under `assets/fonts/special-elite/` under the Apache 2.0 licence.

### Changed
- Removed the built-in Modern grotesque, Editorial serif, and the Charter-plus-Courier pairing. `migrate_stored_options()` maps any retired value to `xcharter`; `mono` still passes through untouched.
- `docs/upgrading.md` webfont deferral is now partially closed. Fonts ship self-hosted, optional, preloaded. Subsetting remains a followup.

### Note on trade-offs
- Bundled font weight is 928 KB, of which XCharter is four weights at ~130 KB each. The theme only ever loads the active stack, so a reader on Monospace incurs no font request. A subsetted rebuild would cut roughly two-thirds off each file; that needs fonttools which was not available when 2.4 shipped, and is recorded as a followup rather than papered over.


## 2.3.0

### Changed
- Default font stack changed to **Editorial serif**. Existing installs keep whatever they had, since `grotesque` remains a valid value that `migrate_stored_options()` does not touch. Fresh installs, and reads that fall back to the default because the stored value is unrecognised, resolve to editorial.


## 2.2.0

### Added
- **Charter body, typewriter headings** font stack. Charter for the argument, Courier Prime falling back to Courier for the headings — a mid-century monospaced typewriter face, contemporary with a Royal Quiet De Luxe. Chosen for graceful degradation: Courier ships on every major platform since 1955, so the visual promise holds even when Courier Prime is not installed.
- **Editorial serif** promoted to a first-class named stack under the slug `editorial`, previously `serif`.
- Body class now surfaces the active font stack as `font-<slug>`, so pairings that need to tune heading rhythm distinctly from single-family stacks can do so without a JavaScript check.
- Scoped adjustments for the typewriter pairing: slightly tighter tracking, reduced line-height, and a 10 percent size reduction on the hero heading, because Courier's proportions make headings that match the serif's optical size read oversized.

### Changed
- `migrate_stored_options()` extended: a stored `font_stack` of `serif` maps to `editorial` on next load. Anyone already on Editorial serif stays on it, transparently.


## 2.1.0

### Changed
- Reduced font stacks to three: **Modern grotesque** (default), **Editorial serif**, and **Monospace**. System UI, Humanist sans, and Serif body sans headings are removed.
- `migrate_stored_options()` extended: a stored `font_stack` of `system`, `humanist`, or `hybrid` maps to `grotesque` on next load. Verified across upgrade cases.

### Notes on the versioning call
- Kept as a minor: no option keys were removed and no user-facing default changed. Only the set of enum values `font_stack` may hold has shrunk. A stored row would degrade gracefully through the sanitiser fallback even without the migration, but migrating in the same commit follows the policy for schema changes.


## 2.0.1

### Fixed
- The hero email icon lost its circular chip and hover state. `protected_mailto()` in JavaScript mode hardcoded `class="mb-email"` into its template and then appended the caller's attributes, producing a second `class` attribute on the same anchor. HTML keeps only the first occurrence, so the caller's `hero-social-link` class was silently discarded and the email icon rendered unstyled while the other icons carried the chip. The builder now merges the caller's class, and `rel`, into single attributes emitted exactly once, verified across all three protection modes.


## 2.0.0

The first major, taken because option keys were removed, which the versioning
policy reserves for a major with a migration routine.

### Changed, breaking
- The colour system is reduced to exactly three choices: **Charcoal**, now the default, **Ivory**, and **Auto**, which renders Ivory when the device prefers light and Charcoal when it prefers dark. The Ink and Custom schemes are removed.
- Removed option keys: `auto_light`, `auto_dark`, `custom_bg`, `custom_text`, `custom_accent`, `custom_muted`, `custom_rule`.
- `migrate_stored_options()` rewrites existing rows on load: removed keys are dropped, and a stored `ink` or `custom` scheme maps to `charcoal`, the default, so unknowns resolve to one value everywhere. Rows already in shape are left untouched, verified across upgrade and clean cases.
- The login screen palette now routes through `resolve_palette()`, taking the light half under Auto.
- Both surviving palettes pass WCAG AA on every declared colour pair, verified computationally: the weakest pair is Ivory muted at 5.19:1 against a 4.5:1 requirement.

### Added, from phase 7
- The options page tab strip is now a real WAI-ARIA tab widget: `role="tablist"` with button tabs, `aria-selected`, `aria-controls`, labelled `role="tabpanel"` regions, a roving tabindex, and arrow, Home, and End key navigation.
- Without JavaScript the options page now renders as one complete long form with the tab strip hidden, replacing the previous behaviour where every panel was invisible.
- The hero portrait is preloaded on the front page with `fetchpriority="high"`, and the image carries explicit dimensions, priority, and async decoding, since it is the largest contentful paint there.


## 1.5.0

### Added
- **Sidebar chrome layout.** A `chrome_layout` option offers a floating rounded card in a left rail holding the wordmark, the navigation, and a stay-in-touch block of text links, leaving the content column free. Topbar remains the default and the full-bleed header is unchanged. The card is sticky within the rail on wide screens, dissolves into an ordinary header below 1024px, and hands over to the existing navigation toggle below 768px.
- **Numbered navigation with keyboard shortcuts.** In the sidebar layout each top-level item carries a boxed digit, 0 through 9, which doubles as its shortcut: pressing the digit follows the link. The digit advertises the shortcut rather than decorating the item. Shortcuts never fire while focus is in an input, textarea, select, or contenteditable region, never under a modifier key, and can be turned off with the `keyboard_nav` option. Items beyond the tenth go unnumbered rather than wrapping.
- Stay-in-touch block renders each populated platform as a text row with an outward arrow, in registry order, with the email entry routed through the obfuscator like everywhere else.

### Changed
- Active social link derivation extracted to `get_active_social_links()`, shared by the hero icon row and the sidebar block.
- The planned-page badge and the navigation digit compose: badge appends at priority 10, digit prepends at 20, so a planned item reads digit, title, badge.


## 1.4.0

### Added
- **Page states.** A page can be marked as planned in its editor sidebar: it stays in the navigation carrying a badge, and its content is replaced by a short notice, with the excerpt serving as the description of what the page will contain. The deliberate inverse of unlisting: an empty site with intent reads as a roadmap. Badge and notice text are configurable on the Advanced tab. State lives in post meta, so it survives a theme switch and is visible to REST.
- **Columns post list style.** Date and section in narrow uppercase monospace columns, then the title. Selected on the Layout tab; the default remains the stacked layout so existing installs are unchanged. Sections render one term per post by construction. Collapses back to stacked below 560px.
- **Auto dark mode.** A fifth colour scheme that pairs a light palette with a dark one and lets the device decide, defaulting to Ivory with Charcoal. The light palette is emitted as the base so browsers without media query support get a complete theme; the dark palette rides `prefers-color-scheme: dark`; `color-scheme: light dark` is declared. The pairing is configurable across every scheme except Auto itself, including Custom.
- **System tab.** Ten rows of theme-owned runtime facts, deliberately not restating Site Health: parser availability, favicon precedence, email protection mode, colour scheme with its Auto pairing, portrait source, metadata ownership including which SEO plugin forced a stand-down, file editor state and what locked it, section slug resolution, and the documentation set. Filterable through `kolofon_system_report`.

### Changed
- Palette resolution extracted to `resolve_palette()`, shared by both blocks of the Auto emission.
- Field `choices` in the options schema may now be a callable, resolved at registration alongside callable help text.


## 1.3.0

### Added
- `kolofon_option_fields` filter registers a field on any tab, through the same code path as the theme's own fields.
- `inc/options-schema.php` declares tabs and fields as data rather than as a sequence of registration calls.

### Changed
- A settings section is now created for every tab automatically, so adding a tab through `kolofon_option_tabs` no longer requires knowing that tabs and sections are separate concepts in the Settings API.
- `register_settings()` reduced from 36 inline registration calls to two loops over the schema.
- The tab list has one source, `get_option_tabs()`, consumed by both the section registration and the rendered tab strip, so the two cannot drift.
- Field help text may be a callable, resolved at registration. Used by the file editor and metadata toggles, which report what `wp-config.php` and the active SEO plugin are doing.

All 45 fields, 42 options plus 3 action buttons, verified present after the refactor, with every default carrying a field and every field a default.


## 1.2.0

### Added
- **Extension points.** Twelve hooks let a child theme or plugin add options, tabs, colour schemes, font stacks, portrait styles, and social platforms, amend the emitted `:root` block, override SEO plugin detection, and inject markup around the hero, all without editing a parent file.
- `kolofon_option_sanitizers` registers a sanitiser callback per option key.

### Fixed
- **The coupling that made extension unsafe.** `opt()` memoised on first call and the sanitiser wrote only keys it named literally, so an option added by a filter would be read from a pre-filter cache and then discarded on the next save. Silent data loss. Two changes resolve it: neither `opt()` nor `get_defaults()` retains a memo until `after_setup_theme` has fired, and sanitising is now driven by a registry keyed on the filtered defaults rather than a hard-coded body.

### Changed
- `get_defaults()` is now a filtered wrapper; the literal values moved to `get_raw_defaults()`.
- `sanitize()` replaced by `sanitize_options()`. Behaviour is unchanged for all 42 existing keys: clamps, enum fallbacks, and checkbox handling verified identical.
- Settings import repointed at the new sanitiser, so imports and form saves continue to share one validation path.


## 1.1.0

### Added
- Hero eyebrow: a short line above the heading in letterspaced monospace capitals, defaulting to empty so nothing changes on an existing install.
- A phrase inside the hero heading can carry the accent colour by wrapping it in `<mark>`. The heading now permits `mark`, `em`, `strong`, and `br` through a dedicated allowlist. `mark` is the semantic element for highlighted text, so assistive technology announces the emphasis rather than the theme faking it with a span. Everywhere the heading is used as plain text it is stripped first.
- Counted pagination replacing directional links: a range and total with chevron controls, so a reader can see they have reached the end of a section.
- Branded login screen. The mark resolves through Custom Logo, then Site Icon, then the bundled portrait, and the colours come from the active scheme rather than a second hard-coded palette. Retires the `login-logo` plugin on the live site.
- Three block patterns: link list, callout, and contact block. The callout is styled with the accent colour and the contact block embeds the protected email shortcode.
- **Meta tags and structured data.** Open Graph, Twitter card, canonical, and description tags, plus a JSON-LD graph carrying `Person`, `WebSite`, and `BlogPosting` on single posts, with `sameAs` populated from the configured social profiles. The share image falls back through featured image, configured portrait, then the bundled portrait.
- SEO plugin detection covering Yoast, Rank Math, All in One SEO, SEOPress, Slim SEO, and The SEO Framework. When one is active the theme stands down entirely and the Advanced tab says which plugin owns the output. Filterable through `kolofon_seo_plugin_active`, which is also the theme's first public filter.
- Translation template at `languages/kolofon.pot`, covering 144 strings across 26 files.
- **Sections.** A new Sections tab establishes mutually exclusive categories: a post belongs to exactly one, and tags carry topics that cut across them. Which categories count as sections, and in what order, is configured as a list of slugs, so nothing is hard-coded.
- Single-category enforcement on `save_post`, which is authoritative and therefore also covers REST, WP-CLI, Quick Edit, and imports. When several categories are present the theme keeps the first configured section in configured order, falling back to the first assigned category. The rule is deterministic.
- `assets/js/single-section.js` makes the block editor category panel behave as a single-choice list, keeping whichever term was added last. The server enforces the same rule regardless, so this is a courtesy rather than a control.
- Section chooser rendered on the home page and on section archives: a row of links to real category archives with the current one marked, carrying post counts. Server-side navigation rather than client-side filtering, so it works without JavaScript and every section keeps a shareable, indexable URL. Scrolls horizontally on phones instead of wrapping into a tall block.
- Previous and next post links now stay inside the current section by default, so a reader following one topic is not dropped into another.
- Tag surfacing, `inc/tags.php`. Tags were always registered, since `post_tag` is core and the theme never removed it, but they only appeared on single posts. They now also appear under each row in post lists, capped at a configurable count with the remainder summarised.
- Tag archives show which sections the tagged posts span, with each named and linked. The section chooser is deliberately suppressed there: a tag archive is the view that crosses sections, so offering a section filter would imply a constraint that does not apply.
- `[menj_tags]` shortcode renders a browsable topic index, selected by popularity and displayed alphabetically. Accepts a `limit` attribute.
- The Sections tab resolves each configured slug and reports whether a category exists for it, with its post count, so a typo surfaces immediately rather than as a section that quietly fails to appear.


## 1.0.0 — 2026-07-24

Initial release of the **menj.bio** theme, forked from the
[Chris Wiegman Theme](https://github.com/ChrisWiegman/chriswiegman-theme) v12.7.0
by Chris Wiegman (GPL v2).

### Added
- Tabbed Theme Options page under **Appearance → Theme Options** with five tabs (Identity, Social, Appearance, Layout, Advanced) built on the Settings API.
- Structured **Social** tab with per-platform URL fields (Mastodon, X, LinkedIn, GitHub, YouTube, Instagram, Facebook, Threads, Pinterest, Email, RSS). Only platforms with a value render.
- Hero renders social links as inline SVG icons in a circular chip layout, hover state driven by the active accent colour.
- Bare-email input on the Email field is normalised to `mailto:` on save.
- Email harvesting protection via `inc/email-guard.php`. Three modes selected on the Social tab: **JavaScript rebuild** (default), **HTML entities**, and **Off**. In JS mode the served HTML contains no address and no `mailto:` scheme; the anchor carries a base64-then-ROT13 payload that `assets/js/email-guard.js` decodes on load. Encoding order is deliberate, so a scraper doing a lone base64 decode recovers non-printable bytes rather than an address-shaped string. Entity mode encodes both the address and the scheme as numeric entities and needs no JavaScript.
- Protection covers three surfaces: the Social tab email icon, any `mailto:` anchor typed into post content (via a `the_content` filter), and a new `[menj_email]` shortcode accepting `address`, `text`, and `class` attributes.
- Decoder script is enqueued only in JS mode.
- **Documentation** tab in Theme Options renders the theme's own `docs/*.md` files inline via a vendored copy of [Parsedown 1.8.0](https://github.com/erusev/parsedown) by Emanuil Rusev (MIT). Sub-nav switches between README and CHANGELOG. Vendored at `vendor/parsedown/`.
- Bundled profile portrait at `assets/img/profile.png` used as:
  - default hero portrait when the option is blank
  - default favicon (respects Site Icon in Settings -> General when set)
  - default apple-touch-icon
- Bundled icon set generated from a face-centred crop of the portrait, flattened onto the accent navy so it stays legible at 16px and visible against dark browser chrome: `favicon.png` (32x32), `icon-192.png` (192x192), `apple-touch-icon.png` (180x180).
- `brand_asset_url()` helper centralises paths to bundled brand assets.
- Development tooling, adapted from the upstream repository's setup: `phpcs.xml` (WordPress standard, VariableAnalysis, PHPCompatibilityWP at 8.0-, prefix and text-domain enforcement), `composer.json` with dev-only dependencies, `Makefile` with `lint`, `fix`, `syntax`, `release`, and `clean` targets, `.gitignore`, and `.editorconfig`.
- GitHub Actions: `lint.yml` runs a parallel syntax sweep then PHPCS on push and pull request, validates `theme.json`, and asserts the four documentation files exist with lowercase names. `release.yml` fires on tag push, refuses to build when the tag disagrees with the `Version:` header in `style.css`, and attaches a clean zip to a GitHub Release.
- Release packaging excludes all development files while retaining `vendor/parsedown/`, which is a runtime dependency rather than a dev one.
- Responsive pass. Three content breakpoints at 1024px, 768px, and 560px replace the previous ad-hoc 860px and 640px rules, and the hover-preview query moved from 900px to 1024px so the reserved gutter cannot squeeze the text column on a tablet.
- Collapsing navigation below 768px, built as progressive enhancement: `assets/js/nav-toggle.js` injects the button and the class the collapsed styles depend on, so without JavaScript the menu stays visible as a wrapping list rather than becoming unopenable. Carries `aria-expanded` and `aria-controls`, closes on Escape with focus returned to the button, closes on link activation, and resets when the viewport leaves the small-screen range. Enqueued only when a primary menu is assigned.
- Hit areas raised to 44px under `(pointer: coarse)`, matching WCAG 2.2 and Apple's guidance.
- Overflow safety: `overflow-wrap` on text elements so long URLs cannot force a horizontal scrollbar, `overflow-x: auto` on content tables, and `max-width: 100%` extended to iframes, embeds, and objects.
- `prefers-reduced-motion` now applies globally rather than to the hover preview alone.
- Admin options page gained responsive rules at 782px, where WordPress collapses its own chrome: tabs and the documentation sub-nav scroll horizontally instead of stacking, inputs go full width, colour presets stack, and wide markdown tables scroll inside the panel.
- `add_theme_support( 'custom-logo' )` for Site Identity uploader; header renders the custom logo when set, otherwise the text site title.
- `Theme URI` and `Update URI` headers point to `https://github.com/menj/kolofon`, reserving the theme slug against wp.org collisions.
- `Author URI` updated to `https://github.com/menj`.
- Documentation tab footer shows repo URL and current theme version.
- `social_github` default pre-filled with `https://github.com/menj`.
- Documentation set standardised to four lowercase files in `docs/`: `readme.md`, `ssot.md`, `upgrading.md`, `changelog.md`. `LICENSE` at the theme root is optional.
- `docs/ssot.md` added: authority map for every option key, colour variable, and platform list, with contributor rules.
- `docs/upgrading.md` added: forward roadmap organised as eight sequenced phases rather than by category. Phase 0 records what shipped; phases 1 to 7 carry twenty-two outstanding items, each with effort, blast radius, prerequisites, and exit criteria. Four items carry an explicitly flagged decision that must be settled before work starts. Separate sections cover deferred items, deliberately-not-planned items, and the versioning policy tying the option schema to semver.
- Documentation tab uses an explicit manifest for order and labels, so `ssot` renders as "SSOT" rather than "Ssot". Extra `.md` files still appear automatically after the manifest set.
- Colour scheme system: Ivory (default), Ink, Charcoal, and Custom, injected as `:root` CSS custom properties on the front-end and in the block editor.
- Font stack option: system sans-serif, editorial serif, or serif body with sans headings.
- Configurable content width, hero heading, hero body, and footer text.
- Lede type sizing on the Appearance tab: `hero_heading_size` (default 56px, range 28 to 96) and `hero_body_size` (default 18px, range 14 to 28). Stored in px, emitted in rem so they honour browser font-size settings. The heading keeps its fluid clamp, with the floor derived as 60% of the ceiling so the scaling stays proportional at any setting.
- `theme.json` v2 with palette bound to CSS variables, fluid typography, and controlled spacing scale.
- Hover previews on post lists. Hovering or keyboard-focusing a row reveals that post's featured image beside the list. Pure CSS, no JavaScript: driven by `:hover` and `:focus-within`, gated behind `(hover: hover) and (pointer: fine)` so touch devices are unaffected, and honouring `prefers-reduced-motion`. Posts without a featured image render no preview. Controlled by `hover_preview` (default on) and `preview_size` (default 240px, range 140 to 400).
- The hovered row lifts into a soft card, tinted with `color-mix()` so it works against every colour preset.
- `inc/post-list.php` added: `post_list_item()` and `post_list_classes()` render every post list in the theme, so the hover markup exists in one place. `home.php`, `index.php`, and `page-blog.php` now share it. Archive rows also gained an optional excerpt dek.
- Two block patterns (`bio-hero`, `bio-card`) and a `kolofon` pattern category.
- Two block styles: hairline separator and accent-bordered quote.
- Modular structure: `inc/` split into `defaults.php`, `setup.php`, `enqueue.php`, `security.php`, `options.php`, `dynamic-css.php`, `blocks.php`.
- Referrer-Policy security header added alongside the existing set.
- Theme File Editor and Plugin File Editor are disabled by default. `DISALLOW_FILE_EDIT` is defined at theme load, both editor submenus are removed, and direct requests to `theme-editor.php` and `plugin-editor.php` are refused with a 403. Controlled by `disable_file_edit` on the Advanced tab, which defaults to on so a fresh install is protected before anyone visits the options page. A definition already present in `wp-config.php` takes precedence and the option cannot loosen it; when that is the case the field says so.
- Single `page-blog.php` template listing posts grouped by year.
- 404 template, custom search form, skip link.

### Phase 1 (roadmap)
- Settings export and import on the Advanced tab. Export streams every setting as pretty-printed JSON; import discards unrecognised keys, then runs the same `sanitize()` the options form uses, so clamps, enum fallbacks, and escaping apply identically to a file and a form post. Both handlers are nonce-checked and gated on `manage_options`. Upload is capped at 256 KB and rejected unless it declares `"theme": "kolofon"`.
- Reset, import, and export now share one status-message system, replacing the single ad-hoc reset notice.
- Documentation rendering hardened: `setMarkupEscaped( true )` alongside safe mode so raw HTML in a source file is escaped rather than emitted; `method_exists()` guards on every Parsedown setter so an older copy loaded by another plugin cannot fatal; and a transient keyed on `md5( slug + filemtime + version + engine )` so an edited file invalidates its own cache. When no parser is available the source is shown escaped inside a `pre` rather than rendering nothing.

### Changed
- Default content width widened from 720px to 1120px for a widescreen layout; clamp range now 600 to 1600.
- Hero portrait size is now a Layout option (`portrait_size`, default 220px, range 120 to 400) exposed as `--mb-portrait`, replacing the hard-coded 260px.
- Hero portrait is no longer forced into a circle. New `portrait_style` Layout option with four values; the default **Floating** applies no mask so a transparent cut-out PNG sits directly on the page background, with a soft `drop-shadow` for separation. Circle, rounded square, and square remain available and crop with `object-fit: cover`.
- Header custom logo no longer forced to a circle.
- Font stacks reworked into six options. Default is now **Modern grotesque** (Helvetica Neue leading a system fallback chain) rather than the plain system UI stack. Added Humanist sans and Monospace; the serif stacks now lead with Charter.
- Hero heading is tighter and heavier: `clamp(2.25rem, 4.5vw, 3.5rem)`, weight 700, `letter-spacing: -0.02em`, `line-height: 1.08`, with `text-wrap: balance`.
- Hero grid is vertically centred with a 4rem gutter; body copy capped at 60ch for readability at wide container sizes.
- Header and footer containers go full-bleed with 2rem side padding, so branding sits hard left and navigation hard right regardless of content width.
- Navigation links weighted 600.
- Section headings ("Recent Posts") now use the accent colour at 1.75rem/700.
- Hero stacking breakpoint raised from 720px to 860px to suit the wider default.
- Namespace `CW\Theme` → `Kolofon`.
- Text domain `chriswiegman-theme` → `kolofon`.
- Hand-written CSS in `assets/css/main.css`; SCSS build removed.
- Enqueue moved out of `functions.php` into `inc/enqueue.php`.
- Front-end styles now consume CSS custom properties instead of hard-coded colours.
- Home template rewritten to consume Theme Options values.

### Removed
- Bluesky from the social platform registry. Any stored `social_bluesky` value is dropped on the next save of the Social tab.
- Chris-specific hard-coded index page IDs (`1226`, `463`) in `save_post` hook.
- CPT column customisations for `talk`, `event`, `location` in `includes/post_columns/`.
- `page-speaking.php` template.
- Yoast-specific filters (`wpseo_next_rel_link`, `wpseo_prev_rel_link`, `wpseo_debug_markers`) — the target site uses Rank Math.
- Compiled SCSS artefacts (`assets/scss/`, `.map` files, `.min.css`).

### Attribution
The following base behaviours are retained from the upstream theme:
- Security headers (`Strict-Transport-Security`, `X-Content-Type-Options`, `X-Frame-Options`, `X-Permitted-Cross-Domain-Policies`).
- Comment removal across post types, admin, and admin bar.
- `intermediate_image_sizes_advanced` trimming of `thumbnail` and `medium_large`.
- `big_image_size_threshold` disabled.
- `wp-embed`, `wp-block-library`, `global-styles`, `classic-theme-styles` dequeued on the front-end.
- `wp_head` cleanup (RSD, WLW manifest, generator, shortlink, oEmbed discovery, REST link).
