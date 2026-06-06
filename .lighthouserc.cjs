const baseUrl = process.env.LIGHTHOUSE_BASE_URL ?? 'http://127.0.0.1:8083';

module.exports = {
    ci: {
        collect: {
            numberOfRuns: 3,
            url: [
                `${baseUrl}/`,
                `${baseUrl}/login`,
            ],
            settings: {
                preset: 'desktop',
                chromeFlags: '--headless=new --no-sandbox',
            },
        },
        assert: {
            assertions: {
                'categories:performance': ['warn', { minScore: 0.7 }],
                'categories:accessibility': ['warn', { minScore: 0.9 }],
                'categories:best-practices': ['warn', { minScore: 0.9 }],
                'categories:seo': ['warn', { minScore: 0.9 }],
            },
        },
        upload: {
            target: 'filesystem',
            outputDir: 'storage/testing/lighthouse',
        },
    },
};
