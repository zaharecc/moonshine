<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core\DependencyInjection;

use Closure;

interface CacheAttributesContract
{
    /**
     * @param  Closure  $default
     * @param  class-string  $target
     * @param  class-string  $attribute
     * @param  int|null  $type
     * @param  string|null  $concrete
     * @param  array<int, string>|null  $column
     *
     * @return mixed
     */
    public function get(
        Closure $default,
        string $target,
        string $attribute,
        ?int $type = null,
        ?string $concrete = null,
        ?array $column = null,
    ): mixed;

    public function resolve(): array;
}
