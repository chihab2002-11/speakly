<?php

test('railway container uses the deployment entrypoint script', function () {
    $dockerfile = file_get_contents(__DIR__.'/../../../Dockerfile');

    expect($dockerfile)->toContain("sed -i 's/\\r$//' /app/docker-entrypoint.sh")
        ->and($dockerfile)->toContain('chmod +x /app/docker-entrypoint.sh')
        ->and($dockerfile)->toContain('CMD ["/app/docker-entrypoint.sh"]');
});

test('deployment entrypoint verifies the database and runs migrations before the php server', function () {
    $entrypoint = file_get_contents(__DIR__.'/../../../docker-entrypoint.sh');

    expect($entrypoint)->toContain('php artisan db:show --no-interaction')
        ->and($entrypoint)->toContain('php artisan migrate --force')
        ->and($entrypoint)->toContain('php artisan optimize:clear')
        ->and($entrypoint)->toContain('exec php -S 0.0.0.0:"${PORT:-8080}" -t public')
        ->and($entrypoint)->toContain('Database connection verification failed')
        ->and($entrypoint)->toContain('Running database migrations...')
        ->and($entrypoint)->toContain('Clearing Laravel optimization caches...');

    expect(strpos($entrypoint, 'php artisan db:show --no-interaction'))->toBeLessThan(strpos($entrypoint, 'php artisan migrate --force'))
        ->and(strpos($entrypoint, 'php artisan migrate --force'))->toBeLessThan(strpos($entrypoint, 'php artisan optimize:clear'))
        ->and(strpos($entrypoint, 'php artisan optimize:clear'))->toBeLessThan(strpos($entrypoint, 'exec php -S 0.0.0.0:"${PORT:-8080}" -t public'));
});

test('required session and sanctum token table migrations exist', function () {
    $migrationContents = collect(glob(__DIR__.'/../../../database/migrations/*.php'))
        ->map(fn (string $migrationPath): string => file_get_contents($migrationPath))
        ->implode("\n");

    expect($migrationContents)->toContain("Schema::create('sessions'")
        ->and($migrationContents)->toContain("Schema::create('personal_access_tokens'");
});
