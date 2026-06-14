import { test, expect } from '@playwright/test';
import { expectNoBrowserIssues, trackBrowserIssues } from './support/browserIssues.js';

async function login(page, email) {
    await page.goto('/login');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: /accedi|login/i }).click();
    await page.waitForURL(/\/dashboard\/?$/);
}

test.describe('Internal CFP flow', () => {
    test('organizer publishes custom internal CFP and speaker submits proposal', async ({ browser }) => {
        const now = Date.now();
        const cfpTitle = `CFP Playwright ${now}`;
        const proposalTitle = `Talk Playwright ${now}`;

        const organizerContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const organizerPage = await organizerContext.newPage();
        const organizerIssues = trackBrowserIssues(organizerPage);

        await login(organizerPage, 'andrea.rossi@email.it');
        await organizerPage.goto('/dashboard/events/7/edit');
        await organizerPage.locator('#website').fill('https://javaancora.it');
        await organizerPage.locator('#tickets_url').fill('https://javaancora.it/tickets');
        await organizerPage.locator('input[wire\\:model\\.live="has_cfp"]').check();
        await organizerPage.locator('#cfp_mode').selectOption('internal');
        await organizerPage.locator('#cfp_status').selectOption('published');
        await organizerPage.locator('#cfp_title').fill(cfpTitle);
        await organizerPage.locator('#cfp_description').fill('CFP interna creata da Playwright.');
        await organizerPage.locator('#cfp_opens_at').fill('01-06-2026 09:00');
        await organizerPage.locator('#cfp_closes_at').fill('31-12-2026 23:59');
        await organizerPage.locator('input[wire\\:model="cfp_fields.0.label"]').fill('Livello browser');
        await organizerPage.locator('input[wire\\:model="cfp_fields.0.key"]').fill('browser_level');
        await organizerPage.locator('select[wire\\:model\\.live="cfp_fields.0.type"]').selectOption('select');
        await organizerPage.locator('textarea[wire\\:model="cfp_fields.0.options_text"]').fill('base\navanzato');
        await organizerPage.getByRole('button', { name: /^salva$/i }).click();

        await organizerPage.waitForURL(/\/dashboard\/communities\/events/);
        await expectNoBrowserIssues(organizerPage, organizerIssues);
        await organizerContext.close();

        const speakerContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const speakerPage = await speakerContext.newPage();
        const speakerIssues = trackBrowserIssues(speakerPage);

        await login(speakerPage, 'anna.verdi@email.it');
        await speakerPage.goto('/events/7');
        await speakerPage.getByRole('link', { name: /cfp/i }).click();
        await speakerPage.locator('#title').fill(proposalTitle);
        await speakerPage.locator('#abstract').fill('Una proposta inviata con Playwright per verificare il flusso completo della CFP interna mobile-first.');
        await speakerPage.locator('select').last().selectOption('avanzato');
        await speakerPage.getByRole('button', { name: /invia candidatura/i }).click();

        await speakerPage.waitForURL(/\/events\/7$/);
        await expect(speakerPage.getByText('Candidatura inviata.')).toBeVisible();
        await expectNoBrowserIssues(speakerPage, speakerIssues);
        await speakerContext.close();

        const reviewContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const reviewPage = await reviewContext.newPage();
        const reviewIssues = trackBrowserIssues(reviewPage);

        await login(reviewPage, 'andrea.rossi@email.it');
        await reviewPage.goto('/dashboard/communities/submissions?event=7');
        await reviewPage.getByText(proposalTitle).click();
        await reviewPage.locator('#status').selectOption('accepted');
        await reviewPage.getByRole('button', { name: /salva stato/i }).click();

        await expect(reviewPage.getByText('Stato aggiornato.')).toBeVisible();
        await expectNoBrowserIssues(reviewPage, reviewIssues);
        await reviewContext.close();
    });
});
