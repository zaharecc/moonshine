<?php

declare(strict_types=1);

namespace MoonShine\Contracts\UI;

use Closure;

interface HasTabMode
{
    public function tabMode(Closure|bool|null $condition = null): static;

    public function isTabMode(): bool;
}