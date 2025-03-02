<?php

declare(strict_types=1);

use Leeto\FastAttributes\Attributes;
use MoonShine\Contracts\Core\DependencyInjection\CacheAttributesContract;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Support\Attributes\Icon;

uses()->group('cache-attributes');

it('resolve', function () {
    $attributes = app(CacheAttributesContract::class);
    $items = $attributes->resolve();
    $userResource = $items[MoonShineUserResource::class][Attribute::TARGET_CLASS];
    $icon = $userResource[Icon::class];

    expect($icon[0])
        ->toBe('users')
    ;
});

it('get attribute', function () {
    $icon = Attributes::for(MoonShineUserResource::class, Icon::class)->first('icon');

    expect($icon)
        ->toBe('users')
    ;
});
