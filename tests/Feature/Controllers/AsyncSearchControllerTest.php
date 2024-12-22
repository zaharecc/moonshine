<?php

declare(strict_types=1);

use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Support\Enums\PageType;
use MoonShine\Tests\Fixtures\Models\Category;
use MoonShine\Tests\Fixtures\Resources\TestCategoryResource;
use MoonShine\Tests\Fixtures\Resources\TestItemResource;
use MoonShine\UI\Fields\StackFields;

uses()->group('async-search-controller');

beforeEach(function (): void {
    $this->itemResource = app(TestItemResource::class);
});

it('async search in form', function () {
    $item = createItem();
    $category = Category::factory()->create([
        'name' => 'test',
    ]);
    $item->categories()->attach($category);
    $item->refresh();
    $resource = app(TestCategoryResource::class);

    $field = StackFields::make()->fields([
        BelongsToMany::make('Categories', resource: $resource)
            ->fillData($item),
    ]);

    addFieldsToTestResource($field);

    asAdmin()->get($this->moonshineCore->getRouter()->to("async-search", [
        'pageUri' => PageType::FORM->value,
        'resourceUri' => 'test-resource',
        'resourceItem' => $item->id,
        '_component_name' => 'test-resource',
        '_relation' => 'categories',
        'query' => 'test',
    ]))
        ->assertOk()
        ->assertJson([
            [
                'value' => $category->getKey(),
                'label' => $category->name,
                'properties' => [
                    'image' => null,
                ],
            ],
        ])
    ;
});

it('async search in index', function () {
    $name = 'test-index-find';

    $item = createItem();
    $category = Category::factory()->create([
        'name' => $name,
    ]);
    $item->categories()->attach($category);
    $item->refresh();

    $response = asAdmin()->get($this->moonshineCore->getRouter()->to("async-search", [
        'pageUri' => PageType::INDEX->value,
        'resourceUri' => $this->itemResource->getUriKey(),
        '_relation' => 'category',
        'query' => 'index-f',
    ]))
        ->assertOk()
        ->assertJsonIsArray()
        ->assertJsonCount(1)
        ->content()
    ;

    $result = json_decode($response, true);

    expect($result[0])
        ->toBeArray()
        ->and($result[0]['label'])
        ->not()->toBeNull()
        ->and($result[0]['label'])
        ->toBe($name)
    ;
});
