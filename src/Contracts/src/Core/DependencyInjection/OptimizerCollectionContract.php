<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core\DependencyInjection;

interface OptimizerCollectionContract
{
    public function getCachePath(): string;

    public function getSource(string $contract, ?string $namespace = null, bool $withCache = true): array;

    public function getSources(?string $namespace = null, bool $withCache = true): array;
}
