<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts\Fields;

use Closure;

interface HasTabModeContract
{
    public function tabMode(Closure|bool|null $condition = null): static;

    public function isTabMode(): bool;
}