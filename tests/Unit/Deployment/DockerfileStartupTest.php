<?php

test('railway container runs migrations before starting the php server', function () {
    $dockerfile = file_get_contents(__DIR__.'/../../../Dockerfile');

    expect($dockerfile)->toContain('php artisan migrate --force')
        ->and($dockerfile)->toContain('php artisan optimize:clear')
        ->and($dockerfile)->toContain('exec php -S 0.0.0.0:${PORT:-8080} -t public');

    expect(strpos($dockerfile, 'php artisan migrate --force'))->toBeLessThan(strpos($dockerfile, 'php artisan optimize:clear'))
        ->and(strpos($dockerfile, 'php artisan optimize:clear'))->toBeLessThan(strpos($dockerfile, 'exec php -S 0.0.0.0:${PORT:-8080} -t public'));
});

test('required session and sanctum token table migrations exist', function () {
    $migrationContents = collect(glob(__DIR__.'/../../../database/migrations/*.php'))
        ->map(fn (string $migrationPath): string => file_get_contents($migrationPath))
        ->implode("\n");

    expect($migrationContents)->toContain("Schema::create('sessions'")
        ->and($migrationContents)->toContain("Schema::create('personal_access_tokens'");
});
