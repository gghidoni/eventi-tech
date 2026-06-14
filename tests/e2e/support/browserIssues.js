import { expect } from '@playwright/test';

export function trackBrowserIssues(page) {
    const issues = [];

    page.on('console', message => {
        if (message.type() !== 'error') {
            return;
        }

        if (
            process.env.PLAYWRIGHT_IGNORE_LOCALHOST_ASSET_FAILURES === '1'
            && message.text().startsWith('Failed to load resource: net::ERR_CONNECTION_REFUSED')
        ) {
            return;
        }

        issues.push(`console error: ${message.text()}`);
    });

    page.on('pageerror', error => {
        issues.push(`page error: ${error.message}`);
    });

    page.on('requestfailed', request => {
        const failure = request.failure();
        const url = request.url();

        if (
            process.env.PLAYWRIGHT_IGNORE_LOCALHOST_ASSET_FAILURES === '1'
            && (url.startsWith('http://localhost:8083/storage/') || url.startsWith('http://127.0.0.1:8083/storage/'))
        ) {
            return;
        }

        issues.push(`request failed: ${request.method()} ${url} (${failure?.errorText ?? 'unknown error'})`);
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
