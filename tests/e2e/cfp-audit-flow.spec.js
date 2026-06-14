import { test, expect } from '@playwright/test';
import { expectNoBrowserIssues, trackBrowserIssues } from './support/browserIssues.js';

async function login(page, email) {
    await page.goto('/login');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: /accedi|login/i }).click();
    await page.waitForURL(/\/dashboard\/?$/);
}

async function fillField(page, index, { label, key, type, options, required = false }) {
    await page.locator(`input[wire\\:model="cfp_fields.${index}.label"]`).fill(label);
    await page.locator(`input[wire\\:model="cfp_fields.${index}.key"]`).fill(key);
    await page.locator(`select[wire\\:model\\.live="cfp_fields.${index}.type"]`).selectOption(type);

    if (required) {
        await page.locator(`input[wire\\:model="cfp_fields.${index}.required"]`).check();
    }

    if (options) {
        await expect(page.locator(`textarea[wire\\:model="cfp_fields.${index}.options_text"]`)).toBeVisible();
        await page.locator(`textarea[wire\\:model="cfp_fields.${index}.options_text"]`).fill(options.join('\n'));
    }
}

async function setDatepickerValue(page, selector, value) {
    await page.locator(selector).evaluate((input, dateValue) => {
        input.removeAttribute('readonly');
        input.value = dateValue;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }, value);
}

test.describe('CFP audit flow', () => {
    test('organizer configures internal CFP, speaker submits, organizer reviews', async ({ browser }) => {
        test.setTimeout(180000);

        const now = Date.now();
        const eventTitle = `Audit CFP Event ${now}`;
        const cfpTitle = `Audit CFP ${now}`;
        const proposalTitle = `Audit Proposal ${now}`;

        const organizerContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const organizerPage = await organizerContext.newPage();
        const organizerIssues = trackBrowserIssues(organizerPage);

        await login(organizerPage, 'andrea.rossi@email.it');

        await organizerPage.goto('/dashboard/events/create');
        await organizerPage.locator('#title').fill(eventTitle);
        await organizerPage.locator('#description').fill('Evento creato da Playwright per audit end-to-end del flusso CFP interno custom.');
        await organizerPage.locator('#type').selectOption('online');
        await setDatepickerValue(organizerPage, 'input[wire\\:model="start_date"]', '15-07-2026 10:00');
        await setDatepickerValue(organizerPage, 'input[wire\\:model="end_date"]', '15-07-2026 12:00');
        await organizerPage.locator('#website').fill('https://event.example.test');

        await organizerPage.locator('input[wire\\:model\\.live="has_cfp"]').check();
        await organizerPage.locator('#cfp_mode').selectOption('internal');
        await organizerPage.locator('#cfp_title').fill(cfpTitle);
        await organizerPage.locator('#cfp_description').fill('CFP interna custom con campi creati da Playwright.');
        await organizerPage.locator('#cfp_opens_at').fill('01-06-2026 09:00');
        await organizerPage.locator('#cfp_closes_at').fill('31-12-2026 23:59');

        await expect(organizerPage.locator('select[wire\\:model\\.live="cfp_fields.0.type"] option[value="file"]')).toHaveCount(0);

        for (let i = 1; i < 9; i += 1) {
            await organizerPage.getByRole('button', { name: /aggiungi campo/i }).click();
            await expect(organizerPage.locator(`input[wire\\:model="cfp_fields.${i}.label"]`)).toBeVisible();
        }

        await fillField(organizerPage, 0, { label: 'Campo testo', key: 'audit_text', type: 'text', required: true });
        await fillField(organizerPage, 1, { label: 'Campo textarea', key: 'audit_textarea', type: 'textarea', required: true });
        await fillField(organizerPage, 2, { label: 'Campo select', key: 'audit_select', type: 'select', options: ['base', 'advanced'], required: true });
        await fillField(organizerPage, 3, { label: 'Campo multiselect', key: 'audit_multiselect', type: 'multiselect', options: ['talk', 'workshop'], required: true });
        await fillField(organizerPage, 4, { label: 'Campo checkbox', key: 'audit_checkbox', type: 'checkbox' });
        await fillField(organizerPage, 5, { label: 'Campo url', key: 'audit_url', type: 'url', required: true });
        await fillField(organizerPage, 6, { label: 'Campo email', key: 'audit_email', type: 'email', required: true });
        await fillField(organizerPage, 7, { label: 'Campo number', key: 'audit_number', type: 'number', required: true });
        await fillField(organizerPage, 8, { label: 'Campo date', key: 'audit_date', type: 'date', required: true });

        await organizerPage.getByRole('button', { name: /^salva$/i }).click();
        await organizerPage.waitForURL(/\/dashboard\/communities\/events/);
        await expect(organizerPage.getByText(eventTitle)).toBeVisible();

        const eventCard = organizerPage.locator('.glass-card').filter({ hasText: eventTitle }).first();
        const eventHref = await eventCard.locator('a').first().getAttribute('href');
        const eventId = eventHref?.match(/\/events\/(\d+)/)?.[1];
        expect(eventId, 'created event id is available from CFP URL').toBeTruthy();
        await organizerContext.close();

        const guestContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const guestPage = await guestContext.newPage();
        const guestIssues = trackBrowserIssues(guestPage);
        await guestPage.goto(`/events/${eventId}`);
        await expect(guestPage.getByRole('link', { name: /cfp/i })).toBeVisible();
        await expect(guestPage.getByRole('link', { name: /cfp/i }).locator('span').last()).not.toHaveText('');
        await guestPage.getByRole('link', { name: /cfp/i }).click();
        await guestPage.waitForURL(/\/login/);
        await expectNoBrowserIssues(guestPage, guestIssues);
        await guestContext.close();

        const speakerContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const speakerPage = await speakerContext.newPage();
        const speakerIssues = trackBrowserIssues(speakerPage);
        await login(speakerPage, 'anna.verdi@email.it');
        await speakerPage.goto(`/events/${eventId}`);
        await speakerPage.getByRole('link', { name: /cfp/i }).click();
        await speakerPage.waitForURL(new RegExp(`/events/${eventId}/cfp/apply`));

        await speakerPage.getByRole('button', { name: /invia candidatura/i }).click();
        await expect(speakerPage.getByText(/obbligatorio|required|abstract/i).first()).toBeVisible();

        await speakerPage.locator('#title').fill(proposalTitle);
        await speakerPage.locator('#abstract').fill('troppo corto');
        await speakerPage.locator('input[type="url"]').fill('not-a-url');
        await speakerPage.locator('input[type="email"]').fill('not-an-email');
        await speakerPage.getByRole('button', { name: /invia candidatura/i }).click();
        await expect(speakerPage.locator('input[type="url"]')).not.toHaveJSProperty('validationMessage', '');

        await speakerPage.locator('#abstract').fill('Abstract valido per la proposta CFP con contenuto sufficiente per superare la validazione minima.');
        await speakerPage.locator('input[type="text"]').last().fill('Risposta testuale');
        await speakerPage.locator('textarea').last().fill('Risposta textarea dettagliata.');
        await speakerPage.locator('select').last().selectOption('advanced');
        await speakerPage.getByText('talk').click();
        await speakerPage.locator('input[type="checkbox"]').last().check();
        await speakerPage.locator('input[type="url"]').fill('https://slides.example.test/audit');
        await speakerPage.locator('input[type="email"]').fill('speaker@example.test');
        await speakerPage.locator('input[type="number"]').fill('45');
        await speakerPage.locator('input[type="date"]').fill('2026-07-01');
        await speakerPage.getByRole('button', { name: /invia candidatura/i }).click();
        await speakerPage.waitForURL(new RegExp(`/events/${eventId}$`));
        await expect(speakerPage.getByText('Candidatura inviata.')).toBeVisible();
        await expectNoBrowserIssues(speakerPage, speakerIssues);
        await speakerContext.close();

        const reviewContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const reviewPage = await reviewContext.newPage();
        const reviewIssues = trackBrowserIssues(reviewPage);
        await login(reviewPage, 'andrea.rossi@email.it');
        await reviewPage.goto('/dashboard/communities/events');
        const reviewCard = reviewPage.locator('.glass-card').filter({ hasText: eventTitle }).first();
        await reviewCard.locator('img[alt="event menu"]').click();
        await reviewCard.getByRole('link', { name: /candidature/i }).click();
        await expect(reviewPage.getByText(proposalTitle)).toBeVisible();
        await reviewPage.getByText(proposalTitle).click();
        await expect(reviewPage.getByText('speaker@example.test')).toBeVisible();
        await reviewPage.locator('#status').selectOption('accepted');
        await reviewPage.getByRole('button', { name: /salva stato/i }).click();
        await expect(reviewPage.getByText('Stato aggiornato.')).toBeVisible();
        await expectNoBrowserIssues(reviewPage, reviewIssues);
        await reviewContext.close();
    });
});
