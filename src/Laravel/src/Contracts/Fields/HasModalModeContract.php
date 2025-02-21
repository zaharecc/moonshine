<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts\Fields;

use Closure;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Core\Collections\Components;

interface HasModalModeContract
{
    /**
     * @param (Closure(static $ctx): bool)|bool|null  $condition
     */
    public function modalMode(Closure|bool|null $condition = null): static;

    public function isModalMode(): bool;

    public function getModalButton(
        Components $components,
        string $label,
        string $fragmentName
    ): ActionButtonContract;
}