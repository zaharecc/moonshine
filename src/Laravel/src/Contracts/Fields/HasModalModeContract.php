<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts\Fields;

use Closure;
use MoonShine\Core\Collections\Components;
use MoonShine\UI\Components\ActionButton;

interface HasModalModeContract
{
    public function modalMode(Closure|bool|null $condition = null): static;

    public function isModalMode(): bool;

    public function getModalButton(
        Components $components,
        string $buttonName,
        string $fragmentName
    ): ActionButton;
}