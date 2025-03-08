<?php

declare(strict_types=1);

use MoonShine\Laravel\Http\Middleware\ChangeLocale;

uses()->group('core');
uses()->group('localization');

beforeEach(function () {
    config()->set('moonshine.locales', ['en', 'foo']);

    app('translator')->setLoaded([
        'moonshine' => [
            'ui' => [
                'en' => ['404' => 'Houston we have a problem page not found'],
                'foo' => ['404' => 'Something message'],
            ],
        ],
    ]);
});

it('checks the localization with the default key name', function () {
    asAdmin()->get('/admin/foo')
        ->assertSee('Houston we have a problem page not found')
        ->assertDontSee('Something message')
        ->assertNotFound();

    asAdmin()->get('/admin/foo?' . http_build_query([ChangeLocale::KEY => 'foo']))
        ->assertDontSee('Houston we have a problem page not found')
        ->assertSee('Something message')
        ->assertNotFound();
});

it('checks the localization with the custom key name', function () {
    config()->set('moonshine.locale_key', 'qwerty');

    asAdmin()->get('/admin/foo')
        ->assertSee('Houston we have a problem page not found')
        ->assertDontSee('Something message')
        ->assertNotFound();

    asAdmin()->get('/admin/foo?' . http_build_query([ChangeLocale::KEY => 'foo']))
        ->assertSee('Houston we have a problem page not found')
        ->assertDontSee('Something message')
        ->assertNotFound();

    asAdmin()->get('/admin/foo?' . http_build_query(['qwerty' => 'foo']))
        ->assertDontSee('Houston we have a problem page not found')
        ->assertSee('Something message')
        ->assertNotFound();
});
