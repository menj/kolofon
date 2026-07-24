// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Smoke tests for kolofon.
 *
 * These are not exhaustive. They verify the theme boots without a PHP fatal,
 * that the home page renders its expected landmarks, and that key routes
 * respond. Adapted from chriswiegman-theme's approach: a few load-bearing
 * assertions per template, rather than a full behaviour matrix.
 */

test.describe('home page', () => {
	test('renders and has a title', async ({ page }) => {
		await page.goto('/');
		await expect(page).toHaveTitle(/.+/);
	});

	test('has hero heading', async ({ page }) => {
		await page.goto('/');
		// The hero heading is an h1 the theme owns on home.php.
		const hero = page.locator('.hero-heading');
		await expect(hero).toBeVisible();
	});

	test('has recent posts section', async ({ page }) => {
		await page.goto('/');
		await expect(page.getByRole('heading', { name: /Recent Posts/i })).toBeVisible();
	});
});

test.describe('blog index', () => {
	test('/blog resolves to a page', async ({ page }) => {
		const response = await page.goto('/blog/');
		expect(response?.status()).toBeLessThan(400);
		// The Blog Index template renders .page-index as its root class.
		await expect(page.locator('.page-index')).toBeVisible();
	});
});

test.describe('theme boot', () => {
	test('no PHP errors visible in output', async ({ page }) => {
		const errors = [];
		page.on('console', msg => {
			if (msg.type() === 'error') errors.push(msg.text());
		});
		await page.goto('/');
		const html = await page.content();
		// PHP notices and fatal errors have distinctive prefixes when
		// display_errors is on. In production they should not appear, but
		// this catches a dev-mode boot failure loudly.
		expect(html).not.toMatch(/Fatal error|Parse error|Warning:.*on line/i);
	});
});
