import { expect } from '@playwright/test';

export function trackBrowserIssues(page) {
    const issues = [];

    page.on('console', message => {
        if (message.type() !== 'error') {
            return;
        }

        issues.push(`console error: ${message.text()}`);
    });

    page.on('pageerror', error => {
        issues.push(`page error: ${error.message}`);
    });

    page.on('requestfailed', request => {
        const failure = request.failure();

        issues.push(`request failed: ${request.method()} ${request.url()} (${failure?.errorText ?? 'unknown error'})`);
    });

    return issues;
}

export async function expectNoBrowserIssues(page, issues) {
    await page.waitForLoadState('networkidle');

    expect(
        issues,
        issues.length === 0 ? 'No browser-side issues detected.' : issues.join('\n')
    ).toEqual([]);
}
