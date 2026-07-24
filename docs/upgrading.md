# Upgrading

The forward plan for this theme, ordered as work rather than as categories.
Each phase states what it delivers, what has to be true before it starts, and
how you know it is finished.

Two rules govern everything below. `inc/defaults.php` stays the single source
of truth, and existing option keys survive across versions. See `ssot.md` for
the full contract.

## Status at a glance

| Phase | Theme | State |
| --- | --- | --- |
| 0 | Fork baseline (1.0.0) | Done |
| 0b | Content architecture (1.1.0) | Done |
| 1 | Groundwork | 2 of 3 shipped |
| 2 | Discoverability | Done in 1.1.0 |
| 3 | Quick presentation wins | Done in 1.1.0 |
| 4 | Extensibility | Done in 1.3.0 |
| 5 | Content features | Done in 1.4.0 |
| 6 | Layout restructure | Done in 1.5.0 |
| 7 | Quality and hardening | 2 of 4 shipped |

Construction is complete. What remains is verification against a real
environment: PHPCS findings (1.3), the core-block dequeue check (7.2), a test
suite (7.3), and an update checker (7.4).

Also shipped outside the numbered plan: content-architecture decisions in
1.1.0 (sections, tags, chooser), the 2.0.0 palette reduction and its migration
routine, hero email icon fix (2.0.1), and the font-stack changes in 2.1.0
through 2.3.0.

---

## Phase 0. Shipped in 1.0.0

Recorded so the roadmap reflects reality rather than intent.

**Coding standards.** `phpcs.xml` layers the WordPress standard with
VariableAnalysis, `Generic.Commenting.Todo`, and PHPCompatibilityWP at
`testVersion 8.0-`, enforcing the `menj_bio` / `MENJ\Bio` / `MENJ_BIO` prefixes
and the `menj-bio` text domain. `composer.json` carries dev dependencies only.

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
one `menj_bio_options` row. A single bad save loses the lot. Drops onto the
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

### 1.3 Work through the PHPCS findings

The ruleset shipped in 1.0.0 but has never run against this codebase. Expect a
first pass of spacing, alignment, and escaping notices. `make fix` clears the
mechanical ones; the escaping findings need reading rather than automating.

*Effort: medium, mostly patience. Blast radius: wide but shallow.*

**Exit criteria.** A settings export round-trips through import and produces an
identical option row (met). The Documentation tab still renders after an edit
to any `.md` file (met). `make lint` passes clean (outstanding, needs a machine
with Composer access).

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
menj.bio runs Rank Math, so shipping without the guard means duplicate tags on
day one.

*Effort: medium. Blast radius: front-end head output only.*

### 2.2 Translation readiness. Shipped in 1.1.0

Generate a `.pot` into `languages/`. `load_theme_textdomain()` is already
called and strings are already wrapped, so this is mostly tooling. Malay
(`ms_MY`) is the obvious first translation.

*Effort: small.*

**Exit criteria.** `languages/menj-bio.pot` exists and covers every wrapped
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
| `menj_bio_defaults` | filter | Amend or extend `get_defaults()` |
| `menj_bio_colour_presets` | filter | Register additional colour schemes |
| `menj_bio_font_stacks` | filter | Register additional font stacks |
| `menj_bio_social_platforms` | filter | Add or remove social platforms |
| `menj_bio_root_css` | filter | Amend the emitted `:root` block |
| `menj_bio_option_tabs` | filter | Register a new options tab |
| `menj_bio_before_hero` / `menj_bio_after_hero` | action | Inject markup around the hero |

**The real problem, stated plainly.** `opt()` caches statically on first call,
and `sanitize()` writes only keys it already knows about. A filtered-in option
would therefore be read before the filter runs, and dropped on the next save.
Solving that coupling is the actual work here; declaring the hooks is the easy
part.

*Effort: medium, with one genuine design problem. Blast radius: touches the
core of the options system.*

### 4.2 Options API generalisation. Shipped in 1.3.0

The options page hard-codes its tabs and registers fields inline. Moving
registration into a declarative array lets `menj_bio_option_tabs` work without
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

### 7.3 Testing

- Unit tests for `sanitize()` covering clamps, enum fallbacks, and the email
  normalisation path.
- A parity assertion that every key in `get_social_platforms()` has a matching
  default, since the field loop, sanitiser, and renderer all depend on it.
- A smoke test that every module named in `$menj_bio_modules` exists.
- Playwright end-to-end coverage when there is enough front-end behaviour to
  warrant it. The upstream repository's `tests/e2e/` is the model.

Partially addressed in 1.0.0: `lint.yml` already asserts the documentation set
is complete and lowercase, and validates `theme.json`.

### 7.4 GitHub release updater

`release.yml` already builds and publishes. This is the consuming half: an
update checker that surfaces a notice under Appearance, Themes when a new tag
is cut.

**Decision required.** Vendor `YahnisElsts/plugin-update-checker` as we did
Parsedown, keeping the zip self-contained at roughly 200 KB, or require a
Composer install step.

---

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
