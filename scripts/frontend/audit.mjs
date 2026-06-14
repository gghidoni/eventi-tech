import fs from "node:fs/promises";
import path from "node:path";
import process from "node:process";

import { launch } from "chrome-launcher";
import lighthouse from "lighthouse";

import config from "../../lighthouse.config.mjs";

const categoryLabels = {
    performance: "performance",
    accessibility: "accessibility",
    "best-practices": "best-practices",
    seo: "seo",
};

function slugFromUrl(url) {
    const { pathname } = new URL(url);
    const slug = pathname === "/" ? "home" : pathname.replace(/^\/+|\/+$/g, "").replace(/[^\w-]+/g, "-");

    return slug || "page";
}

function roundScore(score) {
    return Number((score ?? 0).toFixed(3));
}

function extractThresholds(assertions) {
    return Object.fromEntries(
        Object.entries(assertions).map(([key, value]) => [
            key.replace("categories:", ""),
            value.minScore,
        ]),
    );
}

async function writeReports(outputDir, slug, run, report) {
    const [htmlReport, jsonReport] = Array.isArray(report) ? report : [report];
    const prefix = path.join(outputDir, `${slug}.run-${run}`);

    await fs.writeFile(`${prefix}.html`, htmlReport);
    await fs.writeFile(`${prefix}.json`, jsonReport);
}

function summarizeRuns(runs) {
    const summary = {};

    for (const [category] of Object.entries(categoryLabels)) {
        const total = runs.reduce((carry, run) => carry + run[category], 0);
        summary[category] = roundScore(total / runs.length);
    }

    return summary;
}

function printSummary(pageSummaries, thresholds) {
    const formatPercent = (score) => `${Math.round(score * 100)}%`;
    let hasWarnings = false;

    for (const page of pageSummaries) {
        console.log(`Lighthouse ${page.slug}`);

        for (const [category, label] of Object.entries(categoryLabels)) {
            const score = page.summary[category];
            const threshold = thresholds[category];
            const status = score >= threshold ? "PASS" : "WARN";

            if (status === "WARN") {
                hasWarnings = true;
            }

            console.log(
                `  ${status} ${label}: ${formatPercent(score)} (target ${formatPercent(threshold)})`,
            );
        }
    }

    if (hasWarnings) {
        console.log("Lighthouse thresholds produced warnings.");
        return;
    }

    console.log("Lighthouse thresholds met.");
}

async function run() {
    const { collect, assert, upload } = config;
    const thresholds = extractThresholds(assert.assertions);

    await fs.rm(upload.outputDir, { force: true, recursive: true });
    await fs.mkdir(upload.outputDir, { recursive: true });

    const launchOptions = {
        chromeFlags: collect.settings.chromeFlags,
    };

    if (collect.settings.chromePort > 0) {
        launchOptions.port = collect.settings.chromePort;
    }

    const chrome = await launch(launchOptions);

    try {
        const pageSummaries = [];

        for (const url of collect.urls) {
            const slug = slugFromUrl(url);
            const runs = [];

            for (let runNumber = 1; runNumber <= collect.numberOfRuns; runNumber += 1) {
                const result = await lighthouse(url, {
                    logLevel: "error",
                    output: ["html", "json"],
                    port: chrome.port,
                    preset: collect.settings.preset,
                    onlyCategories: Object.keys(categoryLabels),
                });

                if (!result?.lhr || !result.report) {
                    throw new Error(`Missing Lighthouse report for ${url}`);
                }

                await writeReports(upload.outputDir, slug, runNumber, result.report);

                runs.push(
                    Object.fromEntries(
                        Object.keys(categoryLabels).map((category) => [
                            category,
                            result.lhr.categories[category].score ?? 0,
                        ]),
                    ),
                );
            }

            pageSummaries.push({
                slug,
                summary: summarizeRuns(runs),
            });
        }

        printSummary(pageSummaries, thresholds);
    } finally {
        await chrome.kill();
    }
}

await run();
