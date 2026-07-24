const { defineConfig } = require('@playwright/test');
const path = require('path');

/**
 * Playwright configuration for menj-bio smoke tests.
 *
 * Adapted from the parent theme's approach (chriswiegman-theme 12.9.7): a
 * minimal test suite that verifies the theme boots and renders its expected
 * landmarks, not a full unit/integration harness. The intent is a fast CI
 * check that catches "the site is broken," not a comprehensive test bed.
 *
 * Expects a WordPress dev environment running at http://localhost:8888,
 * which is the wp-env default. Tests can be run against any URL via
 * PLAYWRIGHT_BASE_URL.
 */
module.exports = defineConfig({
	testDir: './specs',
	timeout: 30 * 1000,
	fullyParallel: true,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 2 : 0,
	reporter: [
		['list'],
		['html', { open: 'never', outputFolder: path.join(process.cwd(), 'artifacts', 'html') }],
		['github'],
	],
	use: {
		baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:8888',
		trace: 'on-first-retry',
	},
	projects: [
		{
			name: 'chromium',
			use: { browserName: 'chromium' },
		},
	],
});
