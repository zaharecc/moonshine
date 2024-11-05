<?php

declare(strict_types=1);

use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Enums\HttpMethod;
use MoonShine\Tests\Fixtures\Models\Comment;
use MoonShine\Tests\Fixtures\Models\Item;
use MoonShine\Tests\Fixtures\Resources\TestCommentResource;
use MoonShine\Tests\Fixtures\Resources\TestResourceBuilder;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

uses()->group('resources-feature');
uses()->group('resources-policies');

beforeEach(function (): void {

    $this->item = createItem(1, 1);

    $this->resource = TestResourceBuilder::new(Item::class)
        ->setTestFields([
            ID::make()->sortable(),
            Text::make('Name', 'name')->sortable(),
            HasMany::make(
                'Comments',
                resource: app(TestCommentResource::class)->setTestPolicy(true)
            )->creatable(),
        ])
        ->setTestPolicy(true)
    ;
});

it('policies in index', function () {
    expect($this->resource->isWithPolicy())
        ->toBeTrue();

    $createButton = ActionButton::make(
        __('moonshine::ui.create'),
        $this->resource->getFormPageUrl()
    )
        ->name('resource-create-button')
        ->primary()
        ->icon('plus');

    $resource = $this->resource;

    $massButton = ActionButton::make(
        '',
        url: static fn (): string => $resource->getRoute('crud.massDelete')
    )
        ->name('mass-delete-button')
        ->bulk($this->resource->getListComponentName())
        ->withConfirm(
            method: HttpMethod::DELETE,
            formBuilder: static fn (FormBuilderContract $formBuilder): FormBuilderContract => $formBuilder->when(
                $resource->isAsync(),
                static fn (FormBuilderContract $form): FormBuilderContract => $form->async(
                    events: $resource->getListEventName(
                        $resource->getListComponentName()
                    )
                )
            ),
            name: 'resource-mass-delete-modal'
        )
        ->error()
        ->icon('trash')
        ->showInLine();

    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()
            ->toPage(page: IndexPage::class, resource: $this->resource)
    )
        ->assertOk()
        ->assertSeeHtml($createButton)
        ->assertSeeHtml($massButton)
    ;

});

it('policy in has many', function () {
    $comment = Comment::query()->first();

    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()
            ->toPage(page: FormPage::class, resource: $this->resource, params: ['resourceItem' => $this->item->id])
    )
        ->assertOk()
        ->assertSee($comment->content)
        ->assertDontSee('has-many-modal-comments-create')
        ->assertSee('has-many-modal-mass-delete')
    ;

});

it('policies index forbidden', function () {
    MoonshineUser::query()->where('id', 1)->update([
        'name' => 'Policies test',
    ]);

    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()
            ->toPage(page: IndexPage::class, resource: $this->resource)
    )
        ->assertForbidden();
});

it('policies in detail', function () {
    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()
            ->toPage(page: DetailPage::class, resource: $this->resource, params: ['resourceItem' => $this->item->id])
    )
        ->assertForbidden()
    ;
});
