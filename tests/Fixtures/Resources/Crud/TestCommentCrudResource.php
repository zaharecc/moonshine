<?php

declare(strict_types=1);

namespace MoonShine\Tests\Fixtures\Resources\Crud;

use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

class TestCommentCrudResource extends AbstractTestingCrudResource
{
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Number::make('User id', 'user_id'),
            Text::make('Comment title', 'content')->sortable(),
        ];
    }

    protected function formFields(): iterable
    {
        return $this->indexFields();
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }
}
