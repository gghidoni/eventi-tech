const baseUrl = process.env.LIGHTHOUSE_BASE_URL ?? "http://127.0.0.1:8083";

export default {
    collect: {
        numberOfRuns: 3,
        urls: [
            `${baseUrl}/`,
            `${baseUrl}/login`,
        ],
        settings: {
            preset: "desktop",
            chromePort: Number(process.env.LIGHTHOUSE_CHROME_PORT ?? 0),
            chromeFlags: [
                "--headless=new",
                "--no-sandbox",
            ],
        },
    },
    assert: {
        assertions: {
            "categories:performance": { minScore: 0.7 },
            "categories:accessibility": { minScore: 0.9 },
            "categories:best-practices": { minScore: 0.9 },
            "categories:seo": { minScore: 0.9 },
        },
    },
    upload: {
        outputDir: "storage/testing/lighthouse",
    },
};
