# Upgrading

The forward plan for this theme, ordered as work rather than as categories.
Each phase states what it delivers, what has to be true before it starts, and
how you know it is finished.

Two rules govern everything below. `inc/defaults.php` stays the single source
of truth, and existing option keys survive across versions. See `ssot.md` for
the full contract.

## After installing an update

One step, and one conditional.

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
| 7 | Quality and hardening | 5 shipped, 1 declined |
| 8 | Baseline stabilisation and live verification | P0, in progress |
| 9 | Architecture and control surface | P1, in progress |
| 10 | Publishing and editorial experience | P1, planned |
| 11 | Quality, automation, accessibility, release confidence | P1, planned |
| 12 | Discoverability, integrations, and operations | P2, planned |
| 13 | The Now page, second attempt | P3, deferred |

Construction is complete through 2.x. The 3.x line added the rename, the
social registry additions, a run of layout corrections, and the Now page. The
static-analysis gate (PHPCS, Phase 1.3) closed in 3.7.1, having never actually
needed a live environment. A stub-harness boot test in the 3.11.0 review then
confirmed the theme loads, activates, and boots through the full lifecycle
without a fatal, closing the "will it white-screen on activation" question.
What remains is behaviour against a real database, query, and REST server,
which the harness fakes and cannot prove. That walk-through is Phase 8.3, and
Phases 8 through 13 below carry the forward plan: every item has a phase, a
status, a rationale, and exit criteria where they apply.

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

Extended in 3.7.0 from five tests to twelve, adding coverage for the (now
removed, Phase 13.0) Now page, the compose form's capability gating,
micro-posts, and the 404 template.

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

### 7.5 Boot harness. Shipped in 3.11.2

The activation boot test built during the 3.11.0 review was run once and
discarded; it is now committed under `tests/boot/` as `boot-test.php` plus its
`wordpress-stubs.php` environment, runnable as `php tests/boot/boot-test.php`
with a 0 or 1 exit code. It loads the theme in the real module order, fires
the full lifecycle including the activation hook, and asserts that nothing
fatals, every hook callback is callable, every field type renders, the
50-key sanitiser round-trips, and the typography builders run. The failure
path was verified by injecting a hook that names a missing function and
confirming a non-zero exit. This fills the gap between PHPCS (which only
reads) and the Playwright smoke suite (7.3, which needs a running site): it
catches the fatal-on-activation class of bug with nothing but PHP. The runner
passes the WordPress standard; the stub file, which mirrors core function
names and signatures, is excluded in `phpcs.xml`.

### 7.6 Resilience module. Shipped in 7.3.2

Built in direct response to a real outage: a WP-Piwik / matomo-php-tracker
missing-file fatal thrown inside a `wp_footer` callback took the whole front
end down mid-render, since WordPress runs `wp_head` and `wp_footer` as a flat
list of every active plugin's callbacks with no isolation between them.

`\Kolofon\run_guarded_hook( $hook )` replaces bare `wp_head()` / `wp_footer()`
calls in `header.php` and `footer.php`. It wraps `do_action()` in a
`try`/`catch` for `\Throwable`, since PHP 7 a missing-file fatal throws an
`\Error`, which implements `\Throwable` and is genuinely catchable, unlike
the older un-catchable fatals. On a caught failure, the real error is written
to the PHP error log with the hook name, message, file, and line; the page
continues rendering normally for every visitor; and a small notice, styled
from the theme's real palette tokens with a static CSS fallback for the case
where the fatal itself aborted the token injection, appears in the footer for
logged-in `manage_options` users, leading with a plain-language sentence
("a script that was supposed to load near the top/bottom of the page didn't
load") with the raw exception behind a details toggle.

This is a safety net, not a fix. The underlying plugin bug still needs
fixing; the module only stops one broken plugin from turning into a
site-wide critical error. Documented as hard invariant 10 in `ssot.md`. Its
existence is itself evidence the theme has been carrying live production
traffic since at least this release (8.1, 8.8).

---

## Phase 8. Baseline stabilisation and live verification

**Priority.** P0. **Status.** In progress.
**Goal.** Close the known documentation, single-source-of-truth, consistency,
and live-verification gaps before adding substantial functionality.
**Prerequisites.** None.

### 8.1 Synchronise documentation with the current codebase. In progress

Correct obsolete option-tab descriptions and the outdated eight-tab
references; correct social-registry counts (the registry reached 40 entries in
7.4.0, and older text still says 38); replace obsolete `--mb-*` CSS variable
references with the current `--k-*` design tokens; audit version numbers
across `README.md`, `readme.txt`, this file, `changelog.md`, the theme
headers, and package metadata; and audit every feature claim against the
actual code.

Substantially advanced in 7.4.0, which rebuilt the tab table from
`get_option_tabs()`, replaced the "one filter so far" claim with the real
thirteen, corrected the "no asset build step" line, and documented the Blog
Index and resilience module for the first time. Remaining: the `--mb-*` sweep
and a full version-reference audit.

**Exit criteria.** No known documentation/code discrepancies remain. Social
platform count matches the registry. CSS token documentation matches the
implementation. Tab documentation matches the options UI. Version references
are internally consistent.

### 8.2 Make primary section resolution authoritative. Planned

Audit every use of `get_the_category()` and route authoritative section
display through `get_primary_section()` instead: post lists, metadata,
breadcrumbs, JSON-LD, archives, adjacent-post navigation, and section labels.
Record `get_primary_section()` as the canonical section authority in
`ssot.md`.

Note that the Blog Index ledger added in 7.3.1.1 reads `get_the_category()[0]`
directly for its section label, so it is one of the call sites this item has
to convert.

**Exit criteria.** All user-facing section displays resolve through one
authority. A post in several categories cannot show contradictory primary
sections in different contexts.

### 8.3 Complete live-install verification. Verify live

The boot harness (7.5) confirms the theme cannot white-screen on activation:
modules load in order, hook callbacks are callable, the activation hook fires
clean, every field type renders, and the sanitiser round-trips. What it cannot
prove is behaviour against a real database, a real query, and a real REST
server, because it fakes all three. This item is the walk-through that closes
that gap, on a live install activated against a supported WordPress version.

1. **Core templates.** Home, blog index, single post, single page, category
   archive, tag archive, search results, 404. None has been individually
   walked through against a real database. One piece of indirect evidence
   arrived since this was written: 7.3.2's resilience module (7.6) was built in
   response to a real plugin fatal caught inside `wp_footer` on a live install,
   which confirms the theme has been carrying real traffic with real
   third-party plugins active since at least that release. That is evidence the
   site survived a fault in production, not a check of the eight page types, so
   this stays open until each is actually walked through.
2. **The tag bug.** Confirmed present in the admin, absent from the front end.
   Investigated from the code side across several passes and every path is
   sound: `render_post_tags()` in `inc/tags.php` calls `get_the_tags()` and
   renders `<ul class="tags">`; the call site in `singular.php` is
   unconditional; `singular.php` is the single-post template with no
   `single.php` shadowing it; neither `the_content` filter suppresses output or
   fires on posts. The code cannot explain the absence, so the cause is runtime
   state. The site has no caching plugins, which weakens the stale-HTML theory
   assumed for several passes; PHP OPcache serving pre-change bytecode after an
   FTP upload, or a server-side theme version older than the one audited, both
   remain live possibilities. The `?ver=` query string on the enqueued
   stylesheet reports which version is actually running and is the cheapest way
   to tell those apart. Waits on the site owner.
3. **Core-block styling after dequeue.** Open since 2.0.0, also 7.2 and 8.4
   below. Confirm the dequeued block-library styles leave no core block
   visibly unstyled on a real page that uses them.
4. **Print output.** Print styles shipped in 3.7.0 and have been read, not
   printed. One pass through a browser print preview on a post, the blog
   index, and a 404.
5. **CSP in report-only mode.** Shipped off by default in 3.7.0. Switch
   `csp_mode` to report, watch the console across the whole site, and widen
   the directive list through `kolofon_csp_directives` for whatever embeds,
   analytics, or CDN sources the live site actually loads, before considering
   enforce. Feeds directly into 15.3.
6. **4K and ultrawide layout.** The container caps at `--k-container` (1120px
   default, adjustable to 1600px). Never verified above 1080p.

**Exit criteria.** All core templates render correctly against a real
database. Tag output is resolved. Core blocks remain usable. Print layouts are
verified. CSP report-only produces actionable findings. Desktop layouts remain
coherent beyond 1080p. Nothing here may be marked complete on the strength of
static analysis or stub tests alone.

### 8.4 Reconsider global WordPress asset dequeues. Planned

Audit `wp_deregister_script( 'wp-embed' )`, `wp_dequeue_style(
'wp-block-library' )`, `wp_dequeue_style( 'global-styles' )`, and
`wp_dequeue_style( 'classic-theme-styles' )`. Decide which removals are safely
replaceable, convert overly aggressive ones to conditional behaviour, and test
representative core and third-party blocks.

**Exit criteria.** The theme keeps its performance goals without unnecessarily
breaking legitimate blocks or plugins.

### 8.5 Build an SSOT integrity checker. Planned

A script that fails the build when the single-source-of-truth invariants in
`ssot.md` are violated: every option has a default, a field definition, and a
sanitiser; every social key exists in the registry and has a default; every
configured font exists and its files are present; every CSS variable
referenced exists; every `theme.json` token has a counterpart; documentation
tab names match the implementation; version numbers agree; required
documentation files exist.

This is listed here rather than with the rest of the test automation (Phase
15) because it is the cheapest guard against the exact class of drift Phase 8
exists to clean up, and it needs no live environment. The `blog_per_page`
sanitiser gap found in 7.4.0, where an option shipped in defaults and schema
but not in `get_option_sanitizers()`, is precisely what check one would have
caught.

**Exit criteria.** Running the checker on a clean tree exits zero, and
removing any one of the three registrations for a test option makes it exit
non-zero.

### 8.6 Standing risk. Parsedown upstream revival

After six years without a stable release, upstream shipped 1.8.0 final on 16
February 2026, and that is the exact build bundled in `vendor/` (verified by
feature fingerprint against the release notes: `setStrictMode`,
`allowRawHtmlInSafeMode`, possessive-quantifier ReDoS hardening, recursive
safe-mode sanitisation, and the PHP 8.4 nullable fixes are all present). Safe
mode enabled, admin-only, and it touches only the theme's own documentation
markdown, never front-end or user-submitted content. Runs clean under PHP 8.3
with full error reporting. A 2.0.0 beta exists upstream and is declined for
now: beta status and reworked extension internals, with no gain for rendering
trusted bundled files. Watch for a 2.0 stable and re-evaluate then. No action
scheduled.

### 8.7 Standing risk. Version number outpaces proven maturity

Written at 3.11.x, when the fork lineage's high version numbers (it ran to
12.7.0 in earlier work) implied a stability the never-run-live gap undercut.
The theme is now at 7.4.0, and the resilience module is real evidence the site
has been live and carrying traffic for several releases, though not proof of
maturity by itself. Not a code problem, but worth noting before any public
release: the fastest way to close it is 8.3. No action scheduled.

---

## Phase 9. Architecture and control surface

**Priority.** P1. **Status.** In progress.
**Goal.** Establish clean boundaries between presentation, site functionality,
publishing infrastructure, and external integrations, then expose the
resulting decisions through Theme Options without turning the options page
into a page builder.
**Prerequisites.** Phase 8 substantially complete.

Architecture and the control surface are one phase because they are one
decision made twice. Display rules (9.3) and design tokens (9.4) are what the
options in 9.6 through 9.11 actually set; building the options first would
mean inventing a second, informal model of the same thing and then
reconciling the two. The first five items establish the model, the rest
expose it.

### 9.1 Establish a Kolofon Core abstraction. In progress

Identify functionality that is site-level rather than presentation-level,
define a Core boundary, and move or abstract reusable functionality behind
Core APIs so templates depend on stable interfaces rather than implementation
details. Document which functionality belongs to the theme and which belongs
to Core, so the boundary is a written contract rather than a convention.
Candidate Core domains: sections, page states, identity, metadata,
publishing utilities, REST interfaces, migrations, diagnostics.

This is the architectural expression of a principle the theme already
follows in places and violates in others: content functionality should
survive a theme switch wherever practical.

The written contract is done: `ssot.md` now maps each candidate domain to
what plays that role in the theme today and judges how clean the boundary
already is. Sections and page states are clean; identity and metadata are
coupled to presentation; migrations are ad hoc with no shared runner. The
actual extraction into a separate Core package has not happened and is not
claimed here. This stays in progress until it does.

**Exit criteria.** Presentation and site functionality have explicit
boundaries. Templates do not need to know Core implementation details.

### 9.2 Isolate ActivityPub behind an integration layer. Shipped in 7.5.0

Audit all direct ActivityPub dependencies, define a Fediverse integration
interface, route theme-facing functionality through it, and stop coupling to
ActivityPub's internal classes. Document plugin availability and fallback
behaviour. Evaluate whether the bundled implementation should remain
in-theme at all.

Preferred long-term direction is integration through a separately maintained
plugin, with a strict compatibility abstraction over the bundled copy as the
fallback. Relevant history: 7.3.1.1 moved the engine from `inc/activitypub/`
to `vendor/activitypub/` precisely to stop it reading as first-party code,
and `inc/microblog.php` already defers to the standalone plugin when it is
active. Both were the right shape; this item finished the job.

`inc/fediverse.php` is now the only file that inspects `\Activitypub`
classes, `ACTIVITYPUB_*` constants, or reads and writes the engine's own
`activitypub_*` options. `inc/microblog.php`'s `activitypub_available()` and
`activitypub_plugin_active()` are kept as thin delegates onto it, since both
names are called throughout that file and by the bridge, so nothing else had
to change at the call sites. `class-activitypub-bridge.php`'s
`activitypub_present()` delegates the same way. The bridge's two
`add_filter()` calls onto `activitypub_supported_post_types` and
`activitypub_object_type` are left as direct calls: those are the engine's
own public extension hooks, the intended integration point, not the internal
coupling this item exists to remove.

Evaluating whether the bundled engine should remain in-theme at all is
deliberately not resolved here. That is a distribution decision (bundle
weight against zero-dependency activation) rather than an architectural one,
and the integration layer makes it a cheap decision to revisit later either
way.

**Exit criteria.** The theme does not depend on ActivityPub implementation
internals. The engine can be updated without unrelated theme changes. Both
met: the only reference to engine internals anywhere in the theme is inside
`inc/fediverse.php` itself.

### 9.3 Create a central display-rules architecture. Shipped in 7.5.0, narrower than first scoped

Audit the existing show/hide options and group presentation rules by context:
homepage, single post, page, archive, search. The purpose is to stop the
uncontrolled proliferation of independent booleans, each with its own default,
sanitiser, and scattered template check.

Only two display options exist in the theme today, `show_recent` and
`show_section_chooser`, at three call sites across `home.php` and
`index.php`. `display_rule()` in `inc/display-rules.php` resolves both
through one named function with a `kolofon_display_rule` filter, and all
three call sites route through it. The context grouping this task describes
(homepage, single post, page, archive, search) was not built, on purpose:
with two features and no context-dependent behaviour between them yet,
inventing that taxonomy now would be structure with nothing to organise.
The resolver is shaped to take a context parameter later without a rewrite,
once a display option actually needs one.

**Exit criteria.** Display behaviour is represented consistently, and a new
display option can be added without duplicating logic across templates. Met
for what exists; the exit criteria did not require the context taxonomy, only
consistent representation and no duplicated logic, and both hold.

### 9.4 Formalise the design-token system. Planned

Audit every CSS custom property and `theme.json` token; define canonical
colour, typography, spacing, and layout-width tokens; document the public
token vocabulary; remove obsolete references.

**Exit criteria.** `theme.json`, the CSS, the PHP, and `ssot.md` agree on one
token vocabulary.

### 9.5 Expand template hierarchy where divergence justifies it. Deferred

Evaluate `archive.php`, `category.php`, `tag.php`, `author.php`, `date.php`,
and `search.php` as separate templates, keeping shared rendering helpers
wherever layouts stay identical.

Deferred rather than planned, and the rule is deliberate: do not split
templates for theoretical WordPress purity. Split them when presentation or
behaviour genuinely diverges. Until then the shared helpers are the correct
design and a split would be pure duplication.

### 9.6 Refine the options information architecture. Planned

Proposed structure: Identity, Appearance, Layout, Content, Social, Fediverse,
Advanced, Documentation. Do not add tabs mechanically; consolidate related
settings and keep the administration experience manageable. Note the history
here: 6.3.0 consolidated eight tabs down precisely because the count had grown
past what was navigable, so this item restructures rather than re-expands.

### 9.7 Add homepage section controls. Planned

Enable and disable homepage sections, control their order, headings, item
counts, and presentation mode. Candidate sections: hero, introduction, social,
recent posts, featured post, sections, microblog, selected posts, tags, call
to action, custom page.

Use a controlled section-order system, not drag-and-drop page-builder
behaviour.

### 9.8 Expand navigation controls. Planned

Primary navigation visibility, header logo and site-title visibility, search
visibility, social visibility, sticky or non-sticky header, header density,
mobile navigation behaviour, external-link indicators, numbered navigation,
keyboard shortcut behaviour.

WordPress remains responsible for menu contents; the theme controls only how
those menus are presented.

### 9.9 Expand footer controls. Planned

Footer menu visibility, social links, copyright, attribution, footer call to
action, footer alignment.

### 9.10 Add reading-experience controls. Planned

Reading column width, wide content width, body font size, line height,
paragraph spacing, body alignment, drop cap, first-paragraph treatment,
blockquote style, pull-quote style, footnote style, code style, table style,
caption style. Body alignment defaults to left, with justification optional.

The justification default carries real history: 2.8.3 justified post content
as a purist decision, and 3.3.x reversed it below 640px where it visibly
fails. Keep the default left-aligned and let justification be a choice.

### 9.11 Introduce distinct layout-width tokens. Planned

Separate the site container (configurable to roughly 1600px) from the reading
column (roughly 680px default) and wide content (roughly 960px default).

**Exit criteria.** Site container and reading column are independently
controllable.

---

## Phase 10. Publishing and editorial experience

**Priority.** P1. **Status.** Planned.
**Goal.** Move the theme from a visually controlled theme to a fuller
publishing environment for writers, researchers, and publishers, including
the block-editor surface authors compose in.
**Prerequisites.** 9.3 display rules, 9.4 design tokens, Phase 9.

The editorial blocks (10.8 through 10.11) sit with publishing rather than in
a phase of their own because they present the same data: the
Author/Publisher block draws on the identity and social registries, and a
publication metadata block renders the fields defined in 10.4. Splitting them
across phases would mean designing the metadata layer twice, once for the
template and once for the editor.

### 10.1 Per-post presentation controls. Planned

Per-post overrides for featured image, sharing, tags, date, adjacent-post
navigation, related posts, author, full-width content, narrow reading width,
hide from homepage, feature on homepage, and an optional noindex control. Use
post metadata; do not duplicate global Theme Options.

### 10.2 Featured content system. Planned

Designate a featured post, support it on the homepage, present it with
editorial restraint, and define the behaviour when none exists. Prefer a
typographic treatment over card-heavy layouts.

### 10.3 Related posts engine. Planned

Ranked by same primary section, then shared tags, then recency. Configurable
result count, optional display on single posts. Depends on 8.2 for a
trustworthy notion of primary section.

**Exit criteria.** Related content is relevant without an external
recommendation service.

### 10.4 Publication metadata layer. Planned

Candidate fields: subtitle, series, volume, issue, original publication date,
updated date, reading time, word count, translator, editor, source, licence,
DOI, ISBN, and other identifiers. Presented as an editorial metadata line,
optionally as structured data, and in a print-compatible form.

### 10.5 Footnotes, citations, and bibliography styling. Planned

WordPress footnotes, reference links, citation presentation, bibliography,
back-links, and print-compatible references.

### 10.6 Archive presentation controls. Planned

Year and month grouping, date format, section column, tag column, excerpt or
dek, featured image, sorting, ascending and descending, pagination mode,
compact mode, alphabetical mode.

### 10.7 Develop the Blog Index into a signature publishing interface. Planned

Three modes: Ledger, Index, Archive. Year and month grouping, section and tag
display, excerpt or dek, image option, sorting, pagination, compact
presentation.

Partly under way already. 7.3.1.1 shipped the Ledger mode, the `blog_per_page`
pagination option, and the year grouping, so this item is the generalisation
of work that exists rather than a build from nothing. The governing rule is
the one that release established and then had to fix: every mode must consume
the same underlying query layer. `blog_index_query_args()` in
`inc/post-list.php` is that layer, and the JSON-LD bug it fixed, where the
rendered list and the structured data were built from different queries, is
what happens when a mode implements its own data logic.

### 10.8 Expand editorial block styles and variations. Planned

Candidates: pull quote, footnote and citation, bibliography, timeline,
callout, author card, publication metadata, link collection, article header,
selected works.

### 10.9 Synchronise editor and front-end design. Planned

Match typography, spacing, colours, block styles, and content-width rules, and
ensure `theme.json` tokens correspond to the front-end CSS. The mechanism
already exists: `build_root_css()` emits the same `:root` block to the editor
sheet that it emits to the front end, so this is an audit of coverage rather
than new plumbing.

### 10.10 Create an Author/Publisher block. Planned

Drawing on the identity registry, social registry, Fediverse identity, and
author metadata: portrait, name, role or tagline, biography, identity links,
and `sameAs`-compatible identifiers.

### 10.11 Improve authoring patterns. Planned

Reusable patterns for author pages, publication lists, biographies, contact
and link lists, and selected works.

---

## Phase 11. Quality, automation, accessibility, and release confidence

**Priority.** P1. **Status.** Planned.
**Goal.** Turn the existing testing foundation into a continuous quality gate
covering code integrity, runtime behaviour, visual regressions,
accessibility, security, and documentation.
**Prerequisites.** Phase 8, and Phase 10 so far as there is something to test.

Placed here, as the last P1 phase and ahead of the P2 work, because a
regression gate is worth most when it exists before the surface it has to
protect. Phase 12 adds integrations, media handling, and security policy;
running that against a gate that already exists is cheaper than retrofitting
one afterwards.

### 11.1 Automate option schema tests. Planned

Option export and import round trip, unknown-key rejection, sanitiser round
trip, default preservation, reset behaviour, field rendering, nonce
protection. Extends what the boot harness (7.5) already asserts once at boot
into a repeatable suite.

### 11.2 Expand Playwright smoke testing. Planned

Coverage for homepage, single post, single page, Blog Index, category
archive, tag archive, date archive, search, 404, dark mode, light mode,
mobile navigation, planned page, status, a Fediverse-enabled configuration,
and print preview. The current suite stops well short of this and predates
the Blog Index ledger entirely.

### 11.3 Add visual regression testing. Planned

Capture stable reference screenshots and compare between releases across
desktop and mobile, the major layout modes, typography variants, dark and
light schemes, and representative content types.

### 11.4 Add automated accessibility testing. Planned

Playwright with axe: keyboard navigation, focus visibility, heading
hierarchy, landmarks, ARIA correctness, form labels, contrast, touch-target
size, reduced motion, mobile navigation, search interface.

Accessibility is a release requirement, not an enhancement. Two precedents
already exist and should be folded in rather than duplicated: the
`WP_DEBUG`-gated contrast guard in `inc/dynamic-css.php`, and the 44px
`pointer: coarse` touch-target rule that 7.4.0's mobile audit extended to the
share row.

### 11.5 Add performance regression testing. Planned

CSS size, JS size, font payload, image payload, homepage and single-post
request counts, Core Web Vitals where practical, caching behaviour.

### 11.6 Add security regression testing. Planned

REST capability enforcement, nonce enforcement, CSP behaviour, file-editor
protection, escaping, sanitisation, direct endpoint access, author
enumeration, unauthenticated mutation attempts.

### 11.7 Add documentation consistency testing. Planned

Version consistency, social registry count, option tab names, CSS token
references, feature status, roadmap status, file existence, and code to
documentation feature parity. The automated form of 8.1, and the check that
keeps this document honest once it stops being maintained by hand.

---

## Phase 12. Discoverability, integrations, and operations

**Priority.** P2. **Status.** Planned.
**Goal.** Improve media efficiency, editorial SEO, structured data, and
search; make the Fediverse and security systems coherent and testable; and
surface the resulting state as an operational interface.
**Prerequisites.** 9.2 ActivityPub abstraction, 9.4 design tokens, 8.4 asset
compatibility audit, Phase 10 publication metadata.

Three related concerns in one phase, ordered so nothing points forward.
Discoverability first (12.1 through 12.6), then the Fediverse and security
systems (12.7 through 12.10), then the diagnostics that report on both (12.11
and 12.12). The health dashboard reads the security policy state defined in
12.8, which is why it cannot precede it.

### 12.1 Upgrade image handling. Planned

Store portrait references as attachment IDs where practical, generate
responsive markup with `srcset` and `sizes`, support WordPress-generated WebP
and AVIF where available, responsive dimensions, image metadata, and
focal-point or crop behaviour where appropriate.

`portrait_markup()` in `inc/branding.php` already emits a `<picture>` with a
two-width `srcset` for the bundled portrait, so the pattern is established;
what is missing is attachment-backed sources rather than a bare URL.

**Exit criteria.** The theme does not depend on a bare portrait URL for
responsive image behaviour.

### 12.2 Add editorial SEO overrides. Planned

Per-content meta description, social title, social description, social image,
canonical URL, and robots directives; global defaults for meta description,
social image, and social title format. Stand down when a supported SEO plugin
owns the corresponding output.

Note that `meta_canonical()` gained paginated-view handling in 7.3.1.1; a
per-post canonical override has to compose with that rather than replace it.

### 12.3 Tighten the JSON-LD architecture. Planned

Audit every schema node: `BlogPosting` and `WebPage` relationships,
contradictory entities, `mainEntityOfPage` correctness, Person identity,
`WebSite` identity, `BreadcrumbList`, and archive and search schema. Prefer
semantic HTML plus microformats plus JSON-LD over duplicating Schema.org
microdata.

The `build_collection_schema()` fix in 7.3.1.1, where a listing page's
`ItemList` was built from the wrong query and had never matched what the page
rendered, is the kind of defect this audit exists to find.

### 12.4 Improve search. Planned

Grouped results by type, section, and date; highlighted terms; keyboard
navigation; escape handling; a slash shortcut and a Ctrl or Cmd+K shortcut;
and a no-JavaScript fallback. Result groups: articles, pages, statuses, tags,
sections.

### 12.5 Improve the 404 experience. Planned

Search, return home, blog index or archive, recent posts, section links. Keep
it editorial and restrained rather than gimmicky; the 3.3.x custom 404 with
its numeric backdrop already sets that tone.

### 12.6 Typography refinement and font subsetting. Deferred

Subset the self-hosted fonts while preserving the required Latin Extended-A
coverage, verify every configured stack afterwards, and measure the
reduction. Candidate tooling: `pyftsubset`.

Deferred on a stated condition: execute when the build pipeline can support
subsetting without creating deployment complexity. The theme currently has no
asset build step beyond CSS minification, and the deployment target has no
shell access, so adding a font pipeline now would cost more than the bytes it
saves.

### 12.7 Separate local comments from Fediverse replies. Planned

Local comments stay disabled; federated replies are supported where
ActivityPub is enabled. Define how replies are stored, rendered, moderated,
and made visible, and leave the local comment policy unchanged.

**Exit criteria.** A user can understand the difference between local comments
and federated interaction.

### 12.8 Build a formal security policy layer. Planned

Covering XML-RPC, comments, the file editor, CSP, security headers, REST
exposure, embeds, oEmbed, and author enumeration. Present the security state
as a diagnostic policy matrix rather than settings scattered across tabs.

### 12.9 Improve CSP reporting. Planned

Three modes: off, report-only, enforce. Collect report-only violations,
provide a review mechanism, identify legitimate external sources, allow
controlled directive extension, and promote to enforce only after validation.
Depends on the report-only pass in 8.3.

**Exit criteria.** CSP can be deployed on a real site without guessing which
sources are required.

### 12.10 Audit REST interfaces. Planned

Document public and authenticated endpoints, capability requirements, and
nonce requirements; audit error responses and rate-sensitive operations; and
confirm the theme exposes no unnecessary mutation endpoints. The Microblog
composer's REST surface is the main thing to review.

### 12.11 Expand the health dashboard. Planned

Categories: theme, environment, content, SEO, performance, security,
Fediverse, testing. Reporting version, WordPress and PHP compatibility, HTTPS
state, permalink state, section count, planned-page count, content count,
metadata state, SEO-plugin detection, Fediverse state, font state, asset
state, and security policy state. Status model: PASS, WARNING, OFF, ACTION
REQUIRED.

This is the single health dashboard. An earlier draft carried a second,
overlapping "security and system health dashboard" under the security phase;
the two described one feature with two category lists, so they are merged
here. The security phase contributes the policy state it reports (12.8), it
does not build a separate surface.

### 12.12 Improve system report diagnostics. Planned

Every finding carries a human-readable explanation, a severity, a recommended
action, and a documentation link. No false PASS states where verification
genuinely requires a live environment, which is the same honesty rule 8.3
applies to this document.

---

## Phase 13. The Now page, second attempt

**Priority.** P3. **Status.** Deferred.
**Goal.** Reintroduce the Now and working-on experience only after the core
theme is proven stable, implementing the remaining functionality outside the
main theme where appropriate.
**Prerequisites.** Core theme proven live (8.3), Phase 9 architecture
established, Microblog available independently.

### 13.0 What was removed. Closed in 4.0.0

The Now feature was removed entirely, moot-ing the planned structural split of
`inc/now.php` into four modules: the feature is gone rather than reorganised.
At 1,308 lines carrying five concerns it was the largest module in the theme
and the only part that had never worked against a real database, so removing
it retired both the structural debt and the largest untested surface in one
step. Deleted: `inc/now.php`, `page-now.php`, `assets/js/now-compose.js`, 204
lines of CSS, four option keys, the Now options tab, the page-creation
handler, the `status` post-format declaration that existed only for
micro-posts, and the Now e2e tests. Option count fell from 50 to 46 and hook
callbacks from 70 to 61, both with zero parity gaps.

A follow-up migration (4.0.1) cleans, once, the database residue old Now
versions left behind: the four Now keys inside the settings row, orphaned
`kolofon_now_fallback_*` cache options, and `kolofon_now_feed_*` transients
with their timeout rows. Gated on a one-shot `kolofon_now_cleanup_done` flag
so the LIKE query runs a single time. Verified against a simulated legacy
database: keys stripped, core settings preserved, orphans and transients
deleted, idempotent on a second run.

User content is deliberately not touched by either. If you also want that
gone, the manual steps are: delete the Now page itself (Pages screen; it
renders as an ordinary page since 4.0.0, and deleting it removes its
`_kolofon_now_entries` and `_wp_page_template` meta with it); delete any
micro-posts (Posts, filter by the `now` category); then delete the empty
`now` category (Posts, Categories). Nothing breaks if they stay.

Removed as a sequencing decision, not a verdict on the idea. The full
implementation contract for the original three-layer design (data model,
entry schema, REST surface, caching strategy, CSS class inventory, lifecycle
rules, porting notes) lives in machine-readable form at
`../specs/now-feature-spec.yml`. Treat that file as a historical record of the
full original scope, not as the spec to build from.

### 13.1 Superseded since the removal: the microblogging layer

The original design's third layer was P2-style inline microblogging: a
front-end compose form for the site owner, posting through a REST endpoint
with a no-JavaScript fallback, publishing a real post into a `now` category
carrying a repurposed `status` post-format. The Microblog module
(`inc/microblog.php` and `inc/microblog/`) shipped since and already covers
this ground, better: a dedicated status post type instead of a
category-plus-format workaround, an admin-bar and front-end composer backed by
REST (`class-composer.php`), a timeline view (`class-timeline.php`), and,
beyond anything the Now design had, ActivityPub federation
(`class-activitypub-bridge.php`). Any rebuild that wants inline posting reuses
Microblog's composer and CPT rather than re-implementing them; the
microblogging variants in the old spec file are retired.

### 13.2 What remains open

Two layers Microblog does not touch: the free-form "Working on" prose section
written as ordinary page content, and an aggregated activity feed pulled from
RSS sources (Goodreads, Threads through a bridge service, and one open slot)
with an hourly transient cache backed by a thirty-day fallback copy, plus
manual entries for platforms without RSS (X, Instagram, Facebook). This is a
curation-of-external-activity concern, not a posting concern, and it is the
entire remaining scope of a rebuild. A stale-content noindex fired when the
page went ninety days without an update; that rule still applies to whatever
rebuilds this.

### 13.3 Variants for the rebuild, in order of preference

1. **A companion plugin, `kolofon-now`.** The strongest option and the one the
   3.11.0 structural review already recommended. The remaining feature is
   still behaviour (fetching, caching, a REST-or-cron pipeline), and behaviour
   in a plugin survives a theme switch, versions independently, and cannot
   bloat the theme again. The theme's contribution reduces to a template and
   styles; the plugin's reduces further now that posting is Microblog's job.
2. **Manual entries only.** The smallest useful slice: the page, the "Working
   on" prose, and hand-curated entries. No fetching, no cron, no external
   dependency. Could ship in-theme without repeating the 3.x sprawl, and the
   aggregation layer can arrive later as the plugin.
3. **Full in-theme rebuild.** Only with a real module split for what is left
   (feed fetch, meta storage, rendering; no REST or CPT layer needed, since
   Microblog owns that now) from the first commit, and only after the core
   theme is proven live. The 3.x attempt showed what happens otherwise.

### 13.4 Implementation rules

- Do not recreate the monolithic `inc/now.php` architecture.
- Do not fetch external feeds synchronously during page rendering. The 3.x
  design fetched on request: three cold feeds at a ten-second timeout each
  could hold a page load for thirty seconds.
- Use scheduled or background fetching; page rendering reads cache only.
- Cache cleanup ships with the feature, not after it. The fallback copies
  needed their own prune routine, and when the feature was removed the orphans
  needed a migration (4.0.1) to clean. A plugin gets this for free through its
  uninstall hook; an in-theme build must write the reverse alongside the
  feature.
- Do not duplicate Microblog's composer or CPT.
- Preserve existing legacy content where practical.

### 13.5 Lessons the next attempt inherits

- **Never let one module carry five concerns.** `inc/now.php` reached 1,308
  lines spanning feed fetch, REST, meta storage, rendering, and query rules.
  It worked, and it was still the codebase's clearest structural debt.
- **Build against a live install from the first week.** The feature shipped
  through eight releases without ever touching a real database, and when it
  finally met one, it did not work. Verification debt compounds.
- **Resolve the template by behaviour, not by meta alone.** WordPress applies
  `page-{slug}.php` by slug without writing `_wp_page_template`, so a manually
  created page renders the template while failing every meta-keyed gate. The
  `is_now_page()` resolver written for 3.11.x (meta first, then slug when no
  explicit template overrides) is the correct shape; start from it.

### 13.6 What already exists to build on

User content was deliberately preserved by the removal: any Now page, its
`_kolofon_now_entries` meta, the `now` category, and micro-posts are still in
the database, so a rebuild can adopt them rather than start empty. The 4.0.1
cleanup runs once behind the `kolofon_now_cleanup_done` flag and touches none
of that content, but a rebuild reusing the old option names must account for
the flag having already fired. The changelog entries for 3.4.0 through 3.7.0
document the original design decisions in detail, including the dual-cache
reasoning and the REST permission model, and the 4.0.0 entry records exactly
what was removed and from where.

---

## Cross-phase requirements

These hold across every phase above. Where one of them is already an invariant
in `ssot.md`, that file is authoritative and this is a pointer to it.

**Single source of truth.** `inc/defaults.php` remains authoritative for
option defaults. Existing option keys survive upgrades unless explicitly
deprecated. The social registry remains authoritative for platforms, the font
registry for font configuration, the section registry for sections. Display
rules (9.3) become authoritative for presentation visibility, the primary
section resolver (8.2) for section identity, and design tokens (9.4) for
visual constants.

**Compatibility.** Remain compatible with supported WordPress versions. Do not
assume only theme-generated content exists. Do not break legitimate
third-party blocks without justification. Detect and defer to dedicated SEO
plugins. Detect ActivityPub availability. Avoid hard dependencies on optional
integrations. Site-level content and functionality should survive a theme
switch wherever practical.

**Accessibility.** Keyboard accessible, visible focus, reduced-motion support,
sufficient contrast, touch-friendly controls, semantic headings, accessible
forms, and no JavaScript dependency for core publishing or navigation. A
release requirement, not an enhancement.

**Progressive enhancement.** Navigation usable without JavaScript. Search has
a usable fallback. Publishing forms degrade safely. Core content remains
accessible when enhancement scripts fail.

**Design.** Preserve editorial restraint, typography-first presentation,
minimal visual noise, a strong reading experience, and clear hierarchy. Avoid
sliders, masonry-heavy layouts, excessive animation, large card grids,
gradient-heavy decoration, page-builder behaviour, feature bloat, and dozens
of low-value toggles.

---

## Release strategy

Group completed roadmap work into coherent releases. Do not force one roadmap
item into one release. These milestones are indicative, not commitments;
nothing above is scheduled.

| Milestone | Focus |
| --- | --- |
| 7.x | Phase 8. Baseline stabilisation, documentation consistency, live verification, primary-section authority, dequeue compatibility, SSOT integrity |
| 8.0 | Phase 9. Core boundary, display rules, design tokens, expanded Theme Options, homepage section ordering, navigation and reading-width controls |
| 8.x | Phase 10. Publishing controls, featured content, related posts, publication metadata, Blog Index modes, editorial blocks |
| 9.0 | Phase 11. SSOT validation, option schema tests, visual regression, accessibility CI, performance and security regression, documentation gates |
| 9.x | Phase 12. Media architecture, editorial SEO, JSON-LD, search, Fediverse abstraction, security policy, CSP reporting, health dashboard |
| 10.x and later | Phase 13. Optional Now companion plugin, and further editorial work driven by real usage |

---

## Deferred, conditional on need

Two roadmap items carry an explicit deferral condition rather than a phase
slot of their own. **Font subsetting (12.6)** waits until the build pipeline
can support it without adding deployment complexity, which matters on a target
with no shell access. **Archive template splitting (9.5)** waits until the
templates genuinely diverge; until then the shared helpers are correct and a
split would be duplication. **The Now page rebuild** is Phase 13 in full.

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

**Homepage drag-and-drop.** Section ordering (9.7) is a controlled list, not
a canvas. Drag-and-drop layout editing is page-builder behaviour under another
name and is refused for the same reason full site editing is.

**Toggles for infrastructure that cannot meaningfully be disabled.** The same
reasoning as optional-module toggles above, stated as a standing rule so new
options are judged against it: a toggle is worth adding only when both states
are genuinely useful.

**Duplicating dedicated SEO plugin functionality.** The editorial overrides in
12.2 exist to fill gaps when no SEO plugin is present, and stand down when one
owns the output. Reimplementing what Yoast or Rank Math already does well is
not a goal.

**External recommendation services for related posts.** 10.3 ranks by primary
section, shared tags, and recency, all from local data. Sending reader
behaviour to a third party to improve a related-posts list on a bio microsite
is a bad trade.

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
