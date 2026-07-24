# Tests

Smoke tests for menj-bio, using Playwright.

## Running

Requires a WordPress development environment at `http://localhost:8888` (the
[wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)
default). Point at a different URL with `PLAYWRIGHT_BASE_URL`.

```bash
# From tests/e2e/
npm install --no-save @playwright/test
npx playwright install chromium
npx playwright test

# Against a different URL
PLAYWRIGHT_BASE_URL=https://menj.bio npx playwright test
```

## What's covered

- Home page renders and has a title.
- Hero heading is visible.
- Recent Posts section is present.
- `/blog` resolves to a page carrying the Blog Index template.
- No PHP fatal errors or warnings visible in output.

## What's not covered

Deliberately: unit tests of individual functions, integration tests for the
options API, mock-driven tests of the SEO plugin stand-down. Those would be a
PHPUnit + WordPress test-suite setup, which is worth the effort on a plugin
but not on a small theme.

The smoke tests exist to catch "the site is broken." Everything else lives in
the parity audit that runs before every zip build (see the developer notes in
`docs/upgrading.md`).
