# Upgrading

The forward plan for this theme, ordered as work rather than as categories.
Each phase states what it delivers, what has to be true before it starts, and
how you know it is finished.

Two rules govern everything below. `inc/defaults.php` stays the single source
of truth, and existing option keys survive across versions. See `ssot.md` for
the full contract.

## After installing an update

Two steps, both quick.

1. **Load any admin page once.** Migrations run on `init` and are each gated on
   their own one-shot flag, so this is all they need. As of 7.0.0 that includes
   moving statuses to the renamed post type.

If the Fediverse is switched on, visit Settings then Permalinks once after an
update that changes rewrite rules, then use the live endpoint test on the
Fediverse tab to confirm discovery still resolves.

## Status at a glance

| Phase | Theme | State |
| --- | --- | --- |
| 0 | Fork baseline (1.0.0) | Done |
| 0b | Content architecture (1.1.0) | Done |
| 1 | Groundwork | Done in 3.7.1 |
| 2 | Discoverability | Done in 1.1.0 |
| 3 | Quick presentation wins | Done in 1.1.0 |
| 4 | Extensibility | Done in 1.3.0 |
| 5 | Content features | Done in 1.4.0 |
| 6 | Layout restructure | Done in 1.5.0 |
| 7 | Quality and hardening | 3 shipped, 1 declined |

Construction is complete through 2.x. The 3.x line added the rename, the
social registry additions, a run of layout corrections, and the Now page. The
static-analysis gate (PHPCS, Phase 1.3) closed in 3.7.1, having never actually
needed a live environment. A stub-harness boot test in the 3.11.0 review then
confirmed the theme loads, activates, and boots through the full lifecycle
without a fatal, closing the "will it white-screen on activation" question.
What remains is behaviour against a real database, query, and REST server,
which the harness fakes and cannot prove. See **Open items** below for the
working list, ordered by priority.

The remaining verification unblocks by pointing the theme at a running
WordPress instance rather than by writing more code. The test suite (7.3) was
shipped in 2.8.0 after reading the parent theme's Playwright setup, scoped to
smoke tests rather than the PHPUnit harness I had been overthinking; its
coverage stops at 3.3.x and does not reach the Now page. The GitHub updater
(7.4) was declined in 2.9.8, since manual zip upload is the chosen workflow.

Also shipped outside the numbered plan, since the 2.x and 3.x lines have done
more work than the roadmap anticipated:

- **2.0.x** — palette reduction to Ivory/Charcoal/Auto (2.0.0) with its
  migration routine, plus the hero email icon fix (2.0.1).
- **2.1–2.5** — the font stack line, culminating in the current five stacks
  (Editorial serif default, XCharter, Special Elite, the typewriter hybrid,
  Plaintext) with self-hosted webfonts in 2.4.0 and the typewriter revival
  in 2.5.0.
- **2.5.1** and **2.6.0** — blog index URL resolver and auto-provisioning.
- **2.7.x** — Book WP layout patterns (index list style, section eyebrow) and
  the follow-up fixes for unstyled page-index templates and the search-form
  label.
- **2.8.x** — cwtheme catch-up (Playwright, section filter row, one dequeue,
  hidden search label), plus the typewriter rhythm and full justification
  on post content.
- **2.9.x** — Chris Wiegman's cwplugin patterns (RSS enclosures, fediverse
  meta, Permissions-Policy), plus the extended hover-preview redesign that
  landed at 2.9.6, the login notice palette fix (2.9.5), and the columns
  date format at 2.9.8.
- **3.0.x.** The menj-bio to Kolofon rename, with `migrate_stored_options()`
  copying `menj_bio_options` across and a dismissible confirmation notice.
  Screenshot rebuilt as a typographic specimen carrying no version-locked
  elements. Description settled on "writer's microsite" with the etymology
  as a single parenthetical.
- **3.1–3.2.** Academia.edu and ORCID added to the social registry, both
  with hand-drawn marks. Tags moved to the foot of single posts. Blog page
  stripped to a bare chronological list. "View all" removed as redundant
  with the section chooser. Excerpts dropped from archives. The 42em
  reading-column constraint moved off list containers.
- **3.3.x.** Custom 404 with a display-scale numeric backdrop. Planned
  pages now emit `noindex, follow`, closing a gap open since 1.4.0. Mobile
  justification falls back to left-aligned below 640px, reversing the
  purist decision from 2.8.3 at the breakpoint where it fails.
- **3.4–3.6.** The Now page. RSS aggregation with dual caching, manual
  entries for platforms without feeds, a front-end compose form with
  progressive enhancement over a REST endpoint, and micro-posts that
  publish as real posts in a `now` category with `status` format, excluded
  from the main archive by default. The largest single feature added since
  the fork, and the least verified.

---

## Phase 0. Shipped in 1.0.0

Recorded so the roadmap reflects reality rather than intent.

**Coding standards.** `phpcs.xml` layers the WordPress standard with
VariableAnalysis, `Generic.Commenting.Todo`, and PHPCompatibilityWP at
`testVersion 8.0-`, enforcing the `kolofon` / `Kolofon` / `KOLOFON` prefixes
and the `kolofon` text domain. `composer.json` carries dev dependencies only.

**Release automation.** `lint.yml` runs a parallel syntax sweep then PHPCS on
push and pull request, validates `theme.json`, and fails the build if any
filename in `docs/` contains an uppercase letter. `release.yml` fires on tag
push, refuses to build when the tag disagrees with the `Version:` header, and
attaches a clean zip to a GitHub Release. `Makefile` exposes the same
packaging locally.

**Email harvesting protection.** Three modes on the Social tab. The default
emits no address and no `mailto:` scheme in the served HTML.

**Hover previews on post lists.** Pure CSS, gated on pointer capability,
honouring reduced motion. The featured image is the illustration.

**File editors disabled by default.** `DISALLOW_FILE_EDIT` defined at theme
load, both submenus removed, direct requests refused with a 403. A definition
in `wp-config.php` takes precedence and this cannot loosen it.

**Responsive pass.** Three content breakpoints at 1024px, 768px, and 560px, a
progressive-enhancement navigation toggle, 44px hit areas on coarse pointers,
overflow safety, and responsive rules for the admin options page.

---

## Phase 0b. Shipped in 1.1.0

**Settings export and import.** Roadmap item 1.1. Three nonce-checked,
`manage_options`-gated handlers on the Advanced tab. Import discards
unrecognised keys then runs the same `sanitize()` the form uses, so a file and
a form post are validated identically.

**Documentation rendering hardened.** Roadmap item 1.2. `setMarkupEscaped`
alongside safe mode, `method_exists()` guards on every Parsedown setter, and a
transient keyed on file modification time so an edited document invalidates its
own cache. Falls back to escaped source when no parser is available.

**Sections.** Not on the roadmap when 1.0.0 shipped, added on request.
Mutually exclusive categories with server-side enforcement, a section chooser
rendered as real archive links, and adjacent post links scoped to the current
section. Configured as a list of slugs so nothing is hard-coded.

Sections partially delivers what phase 5.2 anticipated, since the post list now
has a category dimension to display. The meta-column layout in 5.2 is still
outstanding.

---

## Phase 1. Groundwork with no open decisions

**Goal.** Clear the items that need no design conversation, so later phases
start from a clean base.

**Prerequisites.** None.

### 1.1 Settings export and import. Shipped

Three nonce-checked, `manage_options`-gated `admin_post` handlers. Export
streams pretty-printed JSON, import parses and validates against the known key
set, reset already exists.

This matters more here than in a larger theme because every setting lives in
one `kolofon_options` row. A single bad save loses the lot. Drops onto the
existing Advanced tab.

Port from Rodolfo's `inc/options-actions.php`.

*Effort: small. Blast radius: none, purely additive.*

### 1.2 Documentation rendering hardening. Shipped

Three gaps against Rodolfo's `inc/docs.php`:

- `setMarkupEscaped( true )` alongside safe mode, so raw HTML in a source file
  is escaped rather than emitted.
- `method_exists()` guards on every Parsedown setter, so an older bundled copy
  cannot fatal.
- A transient keyed on `md5( slug + filemtime + version + engine )`, so an
  edited file invalidates its own cache.

*Effort: small. Blast radius: none, no behaviour change.*

### 1.3 PHPCS findings. Shipped in 3.7.1

The ruleset shipped in 1.0.0. The codebase now passes the full WordPress
Coding Standard with zero errors and zero warnings.

This was recorded for two years as blocked on Composer, which was wrong.
PHP_CodeSniffer runs from a standalone PHAR, and WPCS with its runtime
dependencies (PHPCSUtils, PHPCSExtra) installs by unzipping tagged releases
and registering an `installed_paths` value. No Composer, no WordPress install.
The single finding was a false-positive nonce flag on the settings-import
`$_FILES` access, verified safe inside `assert_can_manage()` and annotated with
a scoped `phpcs:ignore`.

*Effort: an afternoon, once the false assumption was dropped.*

**Exit criteria.** A settings export round-trips through import and produces an
identical option row (met). The Documentation tab still renders after an edit
to any `.md` file (met). PHPCS passes clean (met in 3.7.1).

---

## Phase 2. Discoverability

**Goal.** Make the site representable when its URL is shared, and translatable.

**Prerequisites.** Phase 1 complete, so linting does not obscure new work.

### 2.1 Meta tags and schema. Shipped

The largest single gap in the theme. There is currently no Open Graph output,
no Twitter card, and no `Person` markup, so a shared link renders as whatever
the receiving platform guesses.

**Scope.** `Person` on the hero, `WebSite`, `og:image` pointing at the bundled
portrait. Not the post-oriented apparatus Rodolfo carries.

**Non-negotiable.** Port `rodolfo_seo_plugin_active()` alongside it. It detects
Yoast, Rank Math, AIOSEO, SEOPress, and The SEO Framework and stands down.
menj.blog runs Rank Math, so shipping without the guard means duplicate tags on
day one.

*Effort: medium. Blast radius: front-end head output only.*

### 2.2 Translation readiness. Shipped in 1.1.0

Generate a `.pot` into `languages/`. `load_theme_textdomain()` is already
called and strings are already wrapped, so this is mostly tooling. Malay
(`ms_MY`) is the obvious first translation.

*Effort: small.*

**Exit criteria.** `languages/kolofon.pot` exists and covers every wrapped
string (met, 144 strings). Deactivating Rank Math produces theme tags and
reactivating it produces exactly one set (verify on the live site). A shared
URL previews correctly on at least two platforms (verify after deploying).

---

## Phase 3. Quick presentation wins

**Goal.** Visible improvement for small effort. Can be done in one sitting.

**Prerequisites.** None, though doing it after phase 1 keeps the linter quiet.

### 3.1 Hero eyebrow. Shipped in 1.1.0

A short letterspaced uppercase line above the hero heading, set in the
monospace stack. It frames the page before the heading speaks.

New key `hero_eyebrow`, defaulting to empty so nothing changes for existing
installs.

*Effort: trivial.*

### 3.2 Accent phrase within the heading. Shipped in 1.1.0

Allow one phrase inside the hero heading to carry the accent colour while the
rest stays in the text colour.

**Decided:** `<mark>` in `hero_heading`, through a four-element allowlist. It
is the semantic element for highlighted text, so assistive technology announces
the emphasis, whereas a shortcode would have produced a styled span carrying no
meaning. The cost is that one option now permits markup, which is recorded as
contributor rule 7 in `ssot.md`.

*Effort: trivial once decided.*

### 3.3 Counted pagination. Shipped in 1.1.0

Replace directional Older and Newer links with a range and total, "1-8 of 8",
plus chevron buttons. On a small site this tells the reader they have seen
everything, which a directional pair never does.

*Effort: small.*

### 3.4 Branded login screen. Shipped in 1.1.0

Replace the wp-login logo with the Custom Logo or Site Icon and tint the
primary button with the accent. Uses media already set rather than a
hand-placed file, and retires the `login-logo` plugin currently installed on
the live site.

Port from Rodolfo's `inc/login.php`.

*Effort: small. Blast radius: wp-login.php only.*

### 3.5 Additional block patterns. Shipped in 1.1.0

Two ship today. Candidates: a link list for an about page, a callout using the
accent colour, a footer contact block.

*Effort: small per pattern.*

**Exit criteria.** Each new option defaults to the current appearance, so an
existing install looks identical until someone changes something.

---

## Phase 4. Extensibility

**Goal.** Give the theme seams, so phase 6 can be built through hooks instead
of branching inside templates.

**Prerequisites.** Phase 1, because this phase edits `opt()` and the sanitiser,
and those edits should land against linted code.

### 4.1 Filter and action surface. Shipped in 1.2.0

A child theme can currently override templates and enqueue CSS, but cannot
alter behaviour without editing parent files. Candidates:

| Hook | Type | Purpose |
| --- | --- | --- |
| `kolofon_defaults` | filter | Amend or extend `get_defaults()` |
| `kolofon_colour_presets` | filter | Register additional colour schemes |
| `kolofon_font_stacks` | filter | Register additional font stacks |
| `kolofon_social_platforms` | filter | Add or remove social platforms |
| `kolofon_root_css` | filter | Amend the emitted `:root` block |
| `kolofon_option_tabs` | filter | Register a new options tab |
| `kolofon_before_hero` / `kolofon_after_hero` | action | Inject markup around the hero |

**The real problem, stated plainly.** `opt()` caches statically on first call,
and `sanitize()` writes only keys it already knows about. A filtered-in option
would therefore be read before the filter runs, and dropped on the next save.
Solving that coupling is the actual work here; declaring the hooks is the easy
part.

*Effort: medium, with one genuine design problem. Blast radius: touches the
core of the options system.*

### 4.2 Options API generalisation. Shipped in 1.3.0

The options page hard-codes its tabs and registers fields inline. Moving
registration into a declarative array lets `kolofon_option_tabs` work without
touching `render_options_page()`.

*Effort: medium. Depends on 4.1.*

**Exit criteria.** A child theme can add an option, a tab, and a colour preset
without editing a parent file, and the added option survives a save.

---

## Phase 5. Content features

**Goal.** Features that reward publishing. Independent of each other, so pick
by appetite.

**Prerequisites.** None, though 5.4 reads better after phase 4.

### 5.1 Page states, "soon" badges. Shipped in 1.4.0

The most useful item on this roadmap for this site specifically. A page can be
marked planned-but-unwritten. It stays in the navigation carrying a badge and
states what it will contain, rather than being hidden until finished.

Given the site has one published post and a great deal of intent, this converts
an empty site into a visible roadmap. Port from Rodolfo's `inc/page-states.php`,
which implements it as the deliberate inverse of unlisting.

*Effort: medium. Blast radius: post meta plus a nav filter, no option schema
change.*

### 5.2 Meta-column post list. Shipped in 1.4.0

Replace the current title-left, date-right row with three aligned columns: date
in monospace, category in monospace accent, then title. Carries more
information in the same space, and the monospace columns give the list a
structural edge a right-floated date does not.

Touches `inc/post-list.php` only, since all three templates already share it.

*Effort: small.*

### 5.3 System report tab. Shipped in 1.4.0

Roughly eight rows, deliberately scoped to theme-owned facts and explicitly not
restating Site Health: Parsedown loaded, Site Icon overriding theme icons,
email obfuscation mode, active colour scheme, portrait source, docs found,
child theme active, asset cache key.

Diagnosing "where is the favicon" during development took four separate checks.
A report answers all four at a glance.

*Effort: medium.*

### 5.4 Automatic dark mode. Shipped in 1.4.0

A fifth colour mode, "Auto", emitting two `:root` blocks wrapped in
`prefers-color-scheme` queries, pairing a light preset with a dark one.

**Decision taken in 1.4.0.** A nominated pair, `auto_light` and `auto_dark`,
defaulting to Ivory and Charcoal. Covers the simple answer out of the box while
keeping the choice open, at the cost of two option keys.

**Later superseded in 2.0.0.** The Ink and Custom schemes were removed and the
pairing fixed to Ivory-with-Charcoal, so `auto_light` and `auto_dark` were
dropped along with them. Left recorded here rather than deleted, because the
1.4.0 choice was defensible on its own terms and the reversal illustrates when
a major bump is worth taking.

If phase 6 lands, the sidebar card is the natural home for a visitor-facing
toggle alongside the automatic default.

*Effort: medium. Blast radius: additive to `get_colour_presets()` and
`build_root_css()`.*

---

## Phase 6. Layout restructure

**Goal.** An alternative chrome layout, offered rather than imposed.

**Prerequisites.** Phase 4. Building this first means writing the layout
branching twice, once in templates and again when hooks arrive.

### 6.1 Sidebar navigation layout. Shipped in 1.5.0

A floating rounded card in a left rail holds the wordmark, the navigation, a
"stay in touch" block, and the mode toggles. All chrome lives in one object and
the content column is left free.

For a site with a handful of pages this reads better than a top bar, and it
gives the social links a permanent home instead of leaving them in the hero.

**Approach.** A `chrome_layout` option with `topbar` as the existing default and
`sidebar` as the alternative. The current full-bleed header stays available.

*Effort: large. Blast radius: the largest on this roadmap. Touches
`header.php`, `footer.php`, every template's container assumptions, and all
three responsive breakpoints.*

### 6.2 Numbered navigation with keyboard shortcuts. Shipped in 1.5.0

Each navigation item carries a boxed digit, 0 through 9, doubling as its
keyboard shortcut. The digit advertises the shortcut rather than decorating it.

**Accessibility constraint.** Single-key shortcuts must not fire while focus is
in an input, and the scheme needs a documented way to disable it. Both are
solved problems, neither is optional.

*Effort: medium. Depends on 6.1, since the numbering only reads well in a
vertical list.*

**Exit criteria.** Switching `chrome_layout` changes the layout with no visual
regression in the other mode, at all three breakpoints.

---

## Phase 7. Quality and hardening

**Goal.** Ongoing rather than sequenced. Pull items forward whenever a phase
above touches the relevant area.

### 7.1 Accessibility audit. Substantially advanced in 2.0.0

Formal pass against WCAG 2.2 AA. Two failures are already known:

- ~~Muted colour against each background.~~ Closed in 2.0.0: the schemes that
  could fail were removed, and both survivors pass AA on every pair, verified
  computationally.
- ~~The options page tab switcher.~~ Closed in 2.0.0: real tablist semantics,
  roving tabindex, arrow, Home, and End keys, and a no-JavaScript fallback that
  shows the whole form.

Also verify focus visibility on the social chips, and reading order with the
skip link.

### 7.2 Performance. Partially shipped in 2.0.0

~~Preload the hero portrait and give it `fetchpriority="high"`.~~ Shipped in
2.0.0, with explicit dimensions on the image. Remaining: confirm the aggressive
dequeues of `wp-block-library`, `global-styles`, and `classic-theme-styles` do
not break pages that legitimately use core blocks, which needs a live install.

### 7.3 Testing. Shipped in 2.8.0

Playwright smoke tests at `tests/e2e/`, adapted from the parent theme's setup
(chriswiegman-theme 12.9.7). Four tests: home page renders, hero heading is
visible, Recent Posts section is present, `/blog` resolves, no PHP errors in
output. Reporter set to list + HTML + GitHub. Config wired to
`@wordpress/scripts` for the base Playwright config.

Deliberately not shipped:

- **Unit tests for `sanitize()`.** Would need PHPUnit + WP-Test-Suite scaffolding
  for a theme small enough that the parity audit (run before every build)
  catches the same regressions faster.
- **Parity assertion for social platforms.** The parity audit already runs
  this check across all option keys, including the `social_*` loop.
- **Module existence smoke.** The PHP `require` in `functions.php` fails loudly
  on any missing module, which is the same signal.

The pattern that shipped is the honest scope for a bio microsite: catch "site
is broken" cheaply, not every possible regression exhaustively.

### 7.4 GitHub release updater. Declined

`release.yml` already builds and publishes. The consuming half — an update
checker that surfaces a notice under Appearance, Themes when a new tag is cut
— was left as a decision between vendoring `YahnisElsts/plugin-update-checker`
(zip self-contained, ~200 KB heavier) and requiring a Composer install step
(zero bloat, more setup for the site owner).

Declined in 2.9.8. Manual zip upload from the GitHub release page is the
chosen workflow. Neither dependency posture is worth the trade for a single-
user microsite where the theme is updated infrequently and by hand. The
`release.yml` output is still useful: it produces the correctly-versioned zip
that gets uploaded through `Appearance > Themes > Add New > Upload Theme`.

Left recorded here rather than removed, because the decision is a real one
and a future maintainer may weigh it differently. Nothing about the theme
would need to change to add this later; the option checker is a standalone
concern.

---

## Open items

The working list as of 3.11.0, ordered by priority. Everything reachable
without a running WordPress instance has been closed; a stub-harness boot test
(added conceptually in the 3.11.0 review) now confirms the theme loads,
activates, and boots through the full lifecycle without a fatal. What remains
splits into a live-install walk-through, one structural refactor, one item
waiting on the site owner, and standing risks.

### Priority 1. Prove it live

The boot harness confirms the theme cannot white-screen on activation: all 27
modules load in order, all 70 hook callbacks are callable, the activation hook
fires clean, every field type renders, and the 50-key sanitiser round-trips
with none lost. What the harness cannot prove is behaviour against a real
database, a real query, and a real REST server, because it fakes all three.
These need a live install on menj.blog, activated against WordPress 6.7.

1. **Confirm the core pages render on a live install.** Home, blog index,
   a single post, a single page, a category archive, a tag archive, search
   results, and the 404. This is the whole surface now that the Now feature is
   gone, and none of it has been exercised against a real database.
2. **Settle the tag bug.** Confirmed present in the admin, absent from the
   front end. Investigated from the code side across several passes and every
   path is sound: `render_post_tags()` in `inc/tags.php` calls `get_the_tags()`
   and renders `<ul class="tags">`; the call site in `singular.php` is
   unconditional; `singular.php` is confirmed as the single-post template with
   no `single.php` shadowing it; neither `the_content` filter suppresses output
   or fires on posts. The code cannot explain the absence, so the cause is
   runtime state rather than the code. The site has no caching plugins, which
   weakens the stale-HTML theory that was assumed for several passes; PHP
   OPcache serving pre-change bytecode after an FTP upload, or a theme version
   on the server older than the one audited, both remain live possibilities.
   The `?ver=` query string on the enqueued stylesheet reports which version is
   actually running and is the cheapest way to tell those apart. Waits on the
   site owner.
3. **Core-block dequeue check (Phase 7.2).** Open since 2.0.0. Confirm the
   block-library styles the theme dequeues do not leave any core block visibly
   unstyled on a real page that uses them.
4. **Print output.** Print styles shipped in 3.7.0 and have been read, not
   printed. One pass through a browser print preview on a post, the Now page,
   and a 404.
5. **CSP in report mode.** Shipped off by default in 3.7.0. Switch `csp_mode`
   to report, watch the browser console across the whole site, and widen the
   directive list through `kolofon_csp_directives` for whatever embeds,
   analytics, or CDN sources the live site actually loads before considering
   enforce.
6. **Layout on 4K and ultrawide.** The container caps at `--k-container`
   (1120px default, adjustable to 1600px). Never verified above 1080p.

### Standing risks, no action scheduled

7. **Parsedown upstream revived; vendored copy is current.** After six years
   without a stable release, upstream shipped 1.8.0 final on 16 February 2026,
   and that is the exact build bundled in `vendor/` (verified by feature
   fingerprint against the release notes: `setStrictMode`,
   `allowRawHtmlInSafeMode`, possessive-quantifier ReDoS hardening, recursive
   safe-mode sanitisation, and the PHP 8.4 nullable fixes are all present).
   Safe mode enabled, admin-only, and it touches only the theme's own
   documentation markdown, never front-end or user-submitted content. Runs
   clean under PHP 8.3 with full error reporting. A 2.0.0 beta exists upstream
   and is declined for now: beta status and reworked extension internals, with
   no gain for rendering trusted bundled files. Watch for a 2.0 stable and
   re-evaluate then.
8. **Version number outpaces proven maturity.** At 3.11.x with a fork lineage
   that ran to 12.7.0 and thirty-plus releases in recent work, the number
   implies a stability the never-run-live gap undercuts. Not a code problem,
   but worth noting before any public release: the fastest way to close it is
   Priority 1.

### Closed in 4.0.0

- **Legacy Now data reverse (4.0.1).** The migration in `inc/hooks.php` now
  cleans, once, the database residue old Now versions left behind: the four
  Now keys inside the settings row, orphaned `kolofon_now_fallback_*` cache
  options (the prune routine that expired them left with the feature), and
  `kolofon_now_feed_*` transients with their timeout rows. Gated on a one-shot
  `kolofon_now_cleanup_done` flag so the LIKE query runs a single time.
  Verified against a simulated legacy database: keys stripped, core settings
  preserved, orphans and transients deleted, idempotent on a second run.

  User content is deliberately not touched. If you also want that gone, the
  manual steps are: delete the Now page itself (Pages screen; it renders as an
  ordinary page since 4.0.0, and deleting it removes its
  `_kolofon_now_entries` and `_wp_page_template` meta with it); delete any
  micro-posts (Posts, filter by the `now` category); then delete the empty
  `now` category (Posts, Categories). Nothing breaks if they stay.

- **The Now feature was removed entirely (was Priority 2).** The planned split
  of `inc/now.php` into four modules is moot: the feature is gone rather than
  reorganised. The removal is a sequencing decision, and the feature returns as
  a planned enhancement: see **Roadmap, The Now feature, second attempt** for
  the rebuild variants and the lessons the next implementation inherits. At
  1,308 lines carrying five concerns it was the largest module
  in the theme and the only part that had never worked against a real database,
  so removing it retires both the structural debt and the largest untested
  surface in one step. Deleted: `inc/now.php`, `page-now.php`,
  `assets/js/now-compose.js`, 204 lines of CSS, four option keys, the Now
  options tab, the page-creation handler, the `status` post-format declaration
  that existed only for micro-posts, and the Now e2e tests. Option count fell
  from 50 to 46 and hook callbacks from 70 to 61, both with zero parity gaps.

### Closed in 3.11.2

- **Boot harness committed as a repeatable gate (was Priority 3).** The
  activation boot test built during the 3.11.0 review was run once and
  discarded; it is now committed under `tests/boot/` as `boot-test.php` plus its
  `wordpress-stubs.php` environment, runnable as `php tests/boot/boot-test.php`
  with a 0 or 1 exit code. It loads the theme in the real module order, fires
  the full lifecycle including the activation hook, and asserts that nothing
  fatals, every hook callback is callable, every field type renders, the
  50-key sanitiser round-trips, and the typography builders run. The failure
  path was verified by injecting a hook that names a missing function and
  confirming a non-zero exit. This fills the gap between PHPCS (which only
  reads) and the Playwright smoke suite (which needs a running site): it catches
  the fatal-on-activation class of bug with nothing but PHP. The runner passes
  the WordPress standard; the stub file, which mirrors core function names and
  signatures, is excluded in `phpcs.xml`.

### Closed in 3.7.1

- **PHPCS findings (Phase 1.3), open since 1.0.0.** The theme now passes the
  full WordPress Coding Standard with zero errors and zero warnings. The task
  had been recorded as blocked on Composer; that was wrong. PHP_CodeSniffer
  distributes as a standalone PHAR, and WPCS plus its runtime dependencies
  (PHPCSUtils, PHPCSExtra) install by unzipping tagged releases and registering
  a path. No Composer, no WordPress install required. One finding surfaced and
  was resolved: a `NonceVerification` flag on the settings-import `$_FILES`
  access, which is a false positive because the nonce is verified inside the
  `assert_can_manage()` helper that PHPCS cannot trace into. Annotated with a
  scoped `phpcs:ignore` carrying that rationale, the documented WPCS pattern for
  a verified-safe access the linter cannot follow.

### Closed in 3.7.0

- `post-formats` support declared for the `status` format.
- `default_favicon_url()` removed as dead code.
- Now feed row markup reduced to one definition, `render_now_feed_row()`,
  shared by page load and the REST response.
- `NOW_FALLBACK_TTL` and `NOW_FETCH_TIMEOUT` made real; both had been declared
  and ignored.
- Orphaned fallback cache options pruned on option save.
- Smoke suite extended from five tests to twelve, covering the Now page, the
  compose form's capability gating, micro-posts, and the 404 template.
- Print styles added.
- Content Security Policy added behind a three-mode option, off by default.

---

## Roadmap

Planned upgrades and enhancements. Nothing here is scheduled; each entry
records what would be built, in which shape, and what the earlier attempt
already settled, so a future implementation starts from lessons rather than
from scratch.

### The Now feature, second attempt

Removed in 4.0.0 as a sequencing decision, not a verdict on the idea. The
core features come first; a "what I'm doing now" surface remains a planned
enhancement, and everything learned from the 3.4 to 3.11 implementation is
recorded here so the next attempt inherits it. The full implementation
contract (data model, entry schema, REST surface, caching strategy, CSS
class inventory, lifecycle rules, porting notes) lives in machine-readable
form at `../specs/now-feature-spec.yml`, written so the feature can be carried
into a Kolofon rebuild, a companion plugin, or another theme entirely.

**What the feature was.** A `/now` page combining three layers: a free-form
"Working on" section written as ordinary page content; an aggregated activity
feed fetched from RSS sources (Goodreads, Threads through a bridge service,
and one open slot) with an hourly transient cache backed by a thirty-day
fallback copy; and manual entries, stored as page meta, for platforms without
RSS (X, Instagram, Facebook). On top of that sat P2-style inline
microblogging: a front-end compose form for the site owner, posting through a
REST endpoint with a no-JavaScript form fallback, able to log an external
link as a manual entry or publish a real post into a `now` category carrying
the `status` format and excluded from the main blog. A stale-content noindex
fired when the page went ninety days without an update.

**Variants for the rebuild, in order of preference.**

1. **A companion plugin, `kolofon-now`.** The strongest option and the one
   the 3.11.0 structural review already recommended. The feature is behaviour
   (fetching, caching, a REST API, query rules), and behaviour in a plugin
   survives a theme switch, versions independently, and cannot bloat the
   theme again. The theme's contribution reduces to a template and styles.
2. **Manual entries only.** The smallest useful slice: the page, the
   "Working on" prose, and hand-curated entries. No fetching, no cron, no
   external dependency. Could ship in-theme without repeating the 3.x
   sprawl, and the aggregation layer can arrive later as the plugin.
3. **Full in-theme rebuild.** Only with the module split (`now-feed`,
   `now-rest`, `now-meta`, `now-render`) from the first commit, and only
   after the core theme is proven live. The 3.x attempt showed what happens
   otherwise.

**Lessons the next attempt inherits.**

- **Never let one module carry five concerns.** `inc/now.php` reached 1,308
  lines spanning feed fetch, REST, meta storage, rendering, and query rules.
  It worked, and it was still the codebase's clearest structural debt.
- **Build against a live install from the first week.** The feature shipped
  through eight releases without ever touching a real database, and when it
  finally met one, it did not work. Verification debt compounds.
- **Resolve the template by behaviour, not by meta alone.** WordPress applies
  `page-{slug}.php` by slug without writing `_wp_page_template`, so a
  manually created page renders the template while failing every meta-keyed
  gate. The `is_now_page()` resolver written for 3.11.x (meta first, then
  slug when no explicit template overrides) is the correct shape; start
  from it.
- **Fetch feeds asynchronously.** The 3.x design fetched on request: three
  cold feeds at a ten-second timeout each could hold a page load for thirty
  seconds. A rebuild fetches on a schedule (WP-Cron or a real cron) and the
  page only ever reads cache.
- **Cache cleanup is part of the feature.** The fallback copies needed their
  own prune routine, and when the feature was removed the orphans needed a
  migration (4.0.1) to clean. A plugin gets this for free through its
  uninstall hook; an in-theme build must write the reverse alongside the
  feature, not after it.

**What already exists to build on.** User content was deliberately preserved
by the removal: any Now page, its `_kolofon_now_entries` meta, the `now`
category, and micro-posts are still in the database, so a rebuild can adopt
them rather than start empty. The 4.0.1 cleanup runs once behind the
`kolofon_now_cleanup_done` flag and touches none of that content, but a
rebuild reusing the old option names must account for the flag having
already fired. The changelog entries for 3.4.0 through 3.7.0 document the
original design decisions in detail, including the dual-cache reasoning and
the REST permission model, and the 4.0.0 entry records exactly what was
removed and from where.

## Deferred, conditional on need

**Talks, events, and locations as a companion plugin.** The forked theme
carried admin column customisations for `talk`, `event`, and `location`.
Stripped in 1.0.0 because the post types were registered elsewhere, leaving
orphaned code.

Confirmed correct: the upstream development repository ships a `pods.json`
declaring all three as Pods post types, so the original author keeps them
outside the theme too. Build only when the content actually exists, and build
it as a plugin so it survives a theme switch.

**Archive and taxonomy template splitting.** `index.php` handles search,
category, tag, and date archives through branching. Splitting is worth doing
only if those archives diverge in design.

**Typography refinement.** Partially delivered in 2.4.0. Two self-hosted
webfonts, XCharter and Special Elite, load only when the active stack asks for
them, with the primary weight preloaded and `font-display: swap`. Subsetting
remains outstanding: files are shipped whole for want of tooling in the build
environment. A rebuild through `pyftsubset --unicodes=U+0000-024F` on both
families would cut each file by roughly two thirds without affecting Latin
Extended-A support.

---

## Deliberately not planned

Recorded so these do not resurface as suggestions.

**Full site editing migration.** Converting to a block theme with
`templates/*.html` would surrender the Theme Options page as the single control
surface and push configuration into the Site Editor, against the theme's
governing principle.

**Comments.** Disabled at every level by design. Re-enabling means restoring
comment templates, moderation surfaces, and spam handling for no benefit on a
bio microsite.

**A CSS build step.** SCSS was removed during the fork. Hand-written CSS with
custom properties covers everything needed and keeps the source readable
without tooling.

**Optional-module toggles.** Rodolfo has sixteen optional modules and a real
need. This theme has eleven, and eight are infrastructure that cannot
meaningfully be disabled. The mechanism would be scaffolding around three
togglable things.

**Generated social cards.** Solves "post has no featured image". A bio site has
one image and it is the portrait. Pointing `og:image` at it in 2.1 is the whole
answer.

**Text and visual mode toggle.** Depends on a commissioned illustration per
post. Without that artwork the toggle is an empty switch.

**Page builder compatibility.** Out of scope.

### A correction worth keeping

Hover-revealed illustration cards were in the rejected list on the same
reasoning as the text and visual toggle, and shipped in 1.0.0 anyway. The
objection was wrong: the illustration needs no commissioning, because WordPress
already has a slot for it. The featured image is the illustration, and posts
without one render no preview. Recorded rather than deleted, because the
failure mode was assuming a dependency that the platform already supplies.

### A second correction worth keeping

The dead Save button on Theme Options was diagnosed in 3.11.5 as the submit
row being left hidden by the Documentation tab's JavaScript, and a CSS-driven
visibility fix shipped for it. The diagnosis was wrong. The actual cause was
the Now tab's "Create Now page" button, which printed an inline `<form>`
inside the settings form. Nested forms are invalid HTML: the browser drops the
inner open tag and the inner close tag ends the outer form early, so every
panel after the Now tab and the Save button itself fell outside any form, and
clicking Save did nothing. It also explains why settings never saved: the form
containing them could not be submitted at all. The bug left with the feature
in 4.0.0, which is why Save started working the moment Now was removed, and
the site owner's observation of that timing is what exposed the wrong
diagnosis. Two things are recorded from it. First, the failure mode: a
plausible latent defect (the hidden submit row was real) was accepted as the
explanation without reproducing the reported symptom against it. Second, the
guard: the boot test now renders the full options page and fails on any
nested form, verified by reinjecting the 3.x markup and watching it fail. The
correct pattern for a second action inside the settings screen is the one the
import button always used: declare the form outside and reference it with the
HTML5 `form` attribute.

**Further extended in 2.10.0.** The "no preview for image-less posts" rule was
itself wrong under scrutiny: on a bio microsite that publishes intermittently,
image-less posts are common and treating them as second-class rows reads badly.
2.10.0 adds a typographic peek for those rows, sharing the same anchor and
shape. The lesson iterates: even reasonable design decisions can compound into
subtle user-experience failures over time.

---

## Versioning policy

Semantic versioning, with the option schema as the contract.

- **Patch** (1.0.x). Fixes and copy changes. No schema change.
- **Minor** (1.x.0). New options, templates, patterns, or hooks. Existing keys
  keep their meaning, new keys resolve to defaults through `opt()`.
- **Major** (x.0.0). Reserved for renaming or removing option keys, or changing
  what an existing key means. Requires a migration routine and a changelog
  entry naming every affected key. Exercised for the first time by 2.0.0, which
  removed seven keys and ships `migrate_stored_options()`.

Any change to `get_defaults()` requires a matching update to `ssot.md` in the
same commit.
