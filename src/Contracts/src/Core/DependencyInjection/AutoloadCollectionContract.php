<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core\DependencyInjection;

use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\ResourceContract;

interface AutoloadCollectionContract
{
    public function getCachePath(): string;

    /**
     * @param  string  $namespace
     * @param  bool  $withCache
     *
     * @return array<string, list<class-string<PageContract|ResourceContract>>>
     */
    public function getSources(string $namespace, bool $withCache = true): array;

    /**
     * @param  class-string<PageContract|ResourceContract>  $contract
     *
     * @return string
     */
    public function getGroupNameByContract(string $contract): string;
}
