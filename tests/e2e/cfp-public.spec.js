import { test, expect } from '@playwright/test';
import { expectNoBrowserIssues, trackBrowserIssues } from './support/browserIssues.js';

test.describe('Seeded CFP public visibility', () => {
    test('published external CFP is visible on the event page', async ({ page }) => {
        const issues = trackBrowserIssues(page);

        await page.goto('/events/5');

        const cfpLink = page.getByRole('link', { name: /cfp/i });

        await expect(page.getByRole('heading', { name: /state management in react con redux/i })).toBeVisible();
        await expect(cfpLink).toBeVisible();
        await expect(cfpLink).toHaveAttribute('href', 'https://cfp.example.test/react-roma');

        await expectNoBrowserIssues(page, issues);
    });

    test('draft and archived external CFPs stay hidden on event pages', async ({ page }) => {
        const response = await page.goto('/events/3');

        expect(response?.status()).toBe(404);
        await expect(page.locator('body')).not.toContainText('https://cfp.example.test/draft-hidden');
        await expect(page.getByRole('link', { name: /cfp/i })).toHaveCount(0);

        const issues = trackBrowserIssues(page);

        await page.goto('/events/11');

        await expect(page.locator('body')).not.toContainText('https://cfp.example.test/archived-hidden');
        await expect(page.getByRole('link', { name: /cfp/i })).toHaveCount(0);
        await expectNoBrowserIssues(page, issues);
    });
});
