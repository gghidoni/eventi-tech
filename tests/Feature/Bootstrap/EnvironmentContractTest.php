<?php

declare(strict_types=1);

test('every explicitly registered application provider is autoloadable', function () {
    $providers = require base_path('bootstrap/providers.php');

    expect($providers)->not->toBeEmpty();

    foreach ($providers as $provider) {
        expect(class_exists($provider), "Provider [{$provider}] is not autoloadable.")->toBeTrue();
    }
});

test('example environment describes the canonical docker runtime', function () {
    $contents = file_get_contents(base_path('.env.example'));

    expect($contents)->toBeString()
        ->and($contents)->toContain('APP_URL=http://127.0.0.1:8083')
        ->and($contents)->toContain('DB_CONNECTION=pgsql')
        ->and($contents)->toContain('DB_HOST=postgres')
        ->and($contents)->toContain('SCOUT_DRIVER=meilisearch')
        ->and($contents)->toContain('MEILISEARCH_URL=http://meilisearch:7700')
        ->and($contents)->toContain('MAIL_HOST=mailpit')
        ->and($contents)->toContain('QUEUE_CONNECTION=sync');
});
