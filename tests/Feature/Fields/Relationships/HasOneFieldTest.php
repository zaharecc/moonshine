<?php

declare(strict_types=1);

uses()->group('model-relation-fields');
uses()->group('has-many-field');

use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Fields\Relationships\HasOne;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Tests\Fixtures\Models\Item;
use MoonShine\Tests\Fixtures\Resources\TestFileResource;
use MoonShine\Tests\Fixtures\Resources\TestResourceBuilder;
use MoonShine\UI\Components\Modal;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

it('modal mode in edit form', function () {
    $item = createItem(countComments: 6);

    $resource = TestResourceBuilder::new(Item::class)->setTestFields([
        ID::make(),
        Text::make('Name'),
        HasOne::make('File', 'itemFile', resource: TestFileResource::class)
            ->modalMode(),
    ]);

    /** @var HasOne $field */
    $field = $resource->getOutsideFields()->filter(fn ($field) => $field->getColumn() === 'itemFile')->first();

    expect($field)
        ->not()->toBeNull()
        ->and($field->isModalMode())
        ->toBeTrue()
    ;

    fakeRequest(
        $this->moonshineCore->getRouter()->getEndpoints()
        ->toPage(
            page: FormPage::class,
            resource: $resource,
            params: ['resourceItem' => $item->id]
        )
    );

    $fieldData = $field->toArray();

    expect($fieldData['component'])
        ->not()->toBeEmpty()
        ->toBeInstanceOf(ActionButtonContract::class)
    ;
});

it('modalMode in edit form with callbacks', function () {
    $item = createItem(countComments: 6);

    $resource = TestResourceBuilder::new(Item::class)->setTestFields([
        ID::make(),
        Text::make('Name'),
        HasOne::make('File', 'itemFile', resource: TestFileResource::class)
            ->modalMode(
                modifyButton: function (ActionButtonContract $button, HasOne $ctx) {
                    $button->customAttributes([
                        'data-button-html' => $ctx->getRelationName() . 'TestRelation',
                    ]);

                    return $button;
                },
                modifyModal: function (Modal $modal, ActionButtonContract $button) {
                    $button->customAttributes([
                        'data-button-html-for-modal' => 'true',
                    ]);
                    $modal->customAttributes([
                        'data-modal-html' => 'true',
                    ]);

                    return $modal;
                }
            ),
    ]);

    asAdmin()
        ->get(
            $this->moonshineCore->getRouter()->getEndpoints()
            ->toPage(
                page: FormPage::class,
                resource: $resource,
                params: ['resourceItem' => $item->id]
            )
        )
        ->assertOk()
        ->assertSee('data-button-html')
        ->assertSee('itemFileTestRelation')
        ->assertSee('data-modal-html')
        ->assertSee('data-button-html-for-modal')
    ;
});

it('modal mode in add form', function () {
    createItem(countComments: 6);

    $resource = TestResourceBuilder::new(Item::class)->setTestFields([
        ID::make(),
        Text::make('Name'),
        HasOne::make('File', 'itemFile', resource: TestFileResource::class)
            ->modalMode(),
    ]);

    /** @var HasOne $field */
    $field = $resource->getOutsideFields()->filter(fn ($field) => $field->getColumn() === 'itemFile')->first();

    expect($field)
        ->not()->toBeNull()
    ;

    fakeRequest(
        $this->moonshineCore->getRouter()->getEndpoints()
        ->toPage(
            page: FormPage::class,
            resource: $resource,
        )
    );

    $fieldData = $field->toArray();

    expect($fieldData['component'])->toBeEmpty();
});

it('disableOutside with modalMode', function () {
    $resource = TestResourceBuilder::new(Item::class)->setTestFields([
        ID::make(),
        Text::make('Name'),
        HasOne::make('File', 'itemFile', resource: TestFileResource::class)
            ->disableOutside(),
    ]);
    expect($resource->getOutsideFields()->isEmpty())
        ->toBeTrue();

    /** @var HasOne $field */
    $field = $resource->getFormFields()->filter(fn ($field) => $field->getColumn() === 'itemFile')->first();
    expect($field)->not()->toBeNull();

    $field->toArray();
    expect($field->isModalMode())->toBeTrue();
});

it('tabMode in edit form', function () {
    $item = createItem(countComments: 6);

    $resource = TestResourceBuilder::new(Item::class)->setTestFields([
        ID::make(),
        Text::make('Name'),
        HasOne::make('File', 'itemFile', resource: TestFileResource::class)
            ->tabMode(),
    ]);

    asAdmin()
        ->get(
            $this->moonshineCore->getRouter()->getEndpoints()
            ->toPage(
                page: FormPage::class,
                resource: $resource,
                params: ['resourceItem' => $item->id]
            )
        )
        ->assertOk()
        ->assertSee('tabs')
    ;
});
