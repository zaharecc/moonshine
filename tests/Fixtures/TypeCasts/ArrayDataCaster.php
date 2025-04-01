<?php

declare(strict_types=1);

namespace MoonShine\Tests\Fixtures\TypeCasts;

use MoonShine\Contracts\Core\Paginator\PaginatorContract;
use MoonShine\Contracts\Core\TypeCasts\DataCasterContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Core\TypeCasts\MixedDataWrapper;

final class ArrayDataCaster implements DataCasterContract
{
    public function __construct(
        protected string $paramKey
    ) {
    }

    public function cast(mixed $data): DataWrapperContract
    {
        if (is_array($data)) {
            return new ArrayDataWrapper($data, $this->paramKey);
        }

        return new MixedDataWrapper($data);
    }

    public function paginatorCast(mixed $data): ?PaginatorContract
    {
        return null;
    }
}
