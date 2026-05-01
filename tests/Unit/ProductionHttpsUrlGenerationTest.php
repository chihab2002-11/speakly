<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    app()['env'] = 'testing';
    config()->set('app.url', 'http://localhost');

    URL::forceRootUrl(null);
    URL::forceScheme(null);
});

afterEach(function () {
    app()['env'] = 'testing';
    config()->set('app.url', 'http://localhost');

    URL::forceRootUrl(null);
    URL::forceScheme(null);
});

test('production forces https urls to the configured app url', function () {
    config()->set('app.url', 'https://speakly.up.railway.app');

    app()['env'] = 'production';

    URL::forceRootUrl(null);
    URL::forceScheme(null);

    (new AppServiceProvider(app()))->boot();

    expect(URL::to('/login'))->toBe('https://speakly.up.railway.app/login')
        ->and(URL::to('/register'))->toBe('https://speakly.up.railway.app/register');
});

test('non production keeps local url generation unchanged', function () {
    config()->set('app.url', 'https://speakly.up.railway.app');

    app()['env'] = 'local';

    URL::forceRootUrl(null);
    URL::forceScheme(null);

    (new AppServiceProvider(app()))->boot();

    expect(URL::to('/login'))->toStartWith('http://')
        ->and(URL::to('/login'))->not->toBe('https://speakly.up.railway.app/login');
});

test('bootstrap trusts forwarded proxy headers for railway', function () {
    $bootstrap = file_get_contents(__DIR__.'/../../bootstrap/app.php');

    expect($bootstrap)->toContain('$middleware->trustProxies(')
        ->and($bootstrap)->toContain("at: '*'")
        ->and($bootstrap)->toContain('Request::HEADER_X_FORWARDED_PROTO')
        ->and($bootstrap)->toContain('Request::HEADER_X_FORWARDED_AWS_ELB');
});
