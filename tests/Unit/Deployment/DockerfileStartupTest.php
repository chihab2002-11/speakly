<?php

test('dockerfile builds frontend assets with composer vendor dependencies available', function () {
    $dockerfile = file_get_contents(__DIR__.'/../../../Dockerfile');

    expect($dockerfile)->toContain('FROM composer:2 AS vendor')
        ->and($dockerfile)->toContain('COPY composer.json composer.lock ./')
        ->and($dockerfile)->toContain('--no-scripts')
        ->and($dockerfile)->toContain('FROM node:22-bookworm-slim AS frontend')
        ->and($dockerfile)->toContain('COPY resources ./resources')
        ->and($dockerfile)->toContain('COPY --from=vendor /app/vendor ./vendor')
        ->and($dockerfile)->toContain('RUN npm ci')
        ->and($dockerfile)->toContain('RUN npm run build');

    expect(strpos($dockerfile, 'FROM composer:2 AS vendor'))->toBeLessThan(strpos($dockerfile, 'FROM node:22-bookworm-slim AS frontend'))
        ->and(strpos($dockerfile, 'COPY --from=vendor /app/vendor ./vendor'))->toBeLessThan(strpos($dockerfile, 'RUN npm run build'));
});

test('dockerfile keeps railway compatible php runtime without baked in startup commands', function () {
    $dockerfile = file_get_contents(__DIR__.'/../../../Dockerfile');

    expect($dockerfile)->toContain('FROM php:8.4-cli')
        ->and($dockerfile)->toContain('mbstring')
        ->and($dockerfile)->toContain('pdo')
        ->and($dockerfile)->toContain('pdo_mysql')
        ->and($dockerfile)->toContain('xml')
        ->and($dockerfile)->toContain('zip')
        ->and($dockerfile)->toContain('COPY --from=frontend /app/public/build ./public/build')
        ->and($dockerfile)->toContain('composer install')
        ->and($dockerfile)->toContain('--no-dev')
        ->and($dockerfile)->toContain('WORKDIR /app')
        ->and($dockerfile)->not->toContain('ENTRYPOINT')
        ->and($dockerfile)->not->toContain('CMD ');
});

test('required session and sanctum token table migrations exist', function () {
    $migrationContents = collect(glob(__DIR__.'/../../../database/migrations/*.php'))
        ->map(fn (string $migrationPath): string => file_get_contents($migrationPath))
        ->implode("\n");

    expect($migrationContents)->toContain("Schema::create('sessions'")
        ->and($migrationContents)->toContain("Schema::create('personal_access_tokens'");
});
