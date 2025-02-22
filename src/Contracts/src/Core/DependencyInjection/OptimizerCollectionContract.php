<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core\DependencyInjection;

use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\ResourceContract;

interface OptimizerCollectionContract
{
    public function getCachePath(): string;

    /**
     * @param  class-string<PageContract|ResourceContract>  $contract
     * @param  string|null  $namespace
     * @param  bool  $withCache
     *
     * @return list<class-string<PageContract|ResourceContract>>
     */
    public function getSource(string $contract, ?string $namespace = null, bool $withCache = true): array;

    /**
     * @param  string|null  $namespace
     * @param  bool  $withCache
     *
     * @return array<string, list<class-string<PageContract|ResourceContract>>>
     */
    public function getSources(?string $namespace = null, bool $withCache = true): array;
}
