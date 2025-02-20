<?php

declare(strict_types=1);

namespace MoonShine\Contracts\UI;

use Closure;

interface HasModalModeContract
{
    public function modalMode(Closure|bool|null $condition = null): static;

    public function isModalMode(): bool;
}