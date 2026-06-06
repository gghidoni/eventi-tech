import { test, expect } from '@playwright/test';
import { expectNoBrowserIssues, trackBrowserIssues } from './support/browserIssues.js';

test.describe('Public pages', () => {
    test('home renders and stays client-error free', async ({ page }) => {
        const issues = trackBrowserIssues(page);

        await page.goto('/');

        await expect(page.getByRole('heading', { name: /scopri,\s*partecipa,\s*connettiti!/i })).toBeVisible();
        await expect(page.locator('section.main-content')).toBeVisible();

        await expectNoBrowserIssues(page, issues);
    });

    test('login page exposes the auth form', async ({ page }) => {
        const issues = trackBrowserIssues(page);

        await page.goto('/login');

        await expect(page.getByRole('heading')).toBeVisible();
        await expect(page.locator('form')).toBeVisible();
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.getByRole('button', { name: /accedi|login/i })).toBeVisible();

        await expectNoBrowserIssues(page, issues);
    });
});
