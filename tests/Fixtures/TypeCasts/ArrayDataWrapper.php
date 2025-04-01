<?php

declare(strict_types=1);

namespace MoonShine\Tests\Fixtures\TypeCasts;

use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;

final class ArrayDataWrapper implements DataWrapperContract
{
    public function __construct(
        protected array $data,
        protected string $paramKey,
    ) {
    }

    public function getOriginal(): array
    {
        return $this->data;
    }

    public function getKey(): int|string|null
    {
        return $this->data[$this->paramKey] ?? null;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
