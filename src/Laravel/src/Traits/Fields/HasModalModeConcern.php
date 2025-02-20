<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Traits\Fields;

use Closure;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Core\Collections\Components;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\UI\Components\ActionButton;

trait HasModalModeConcern
{
    protected bool $isModalMode = false;

    /**
     * @param (Closure(static $ctx): bool)|bool|null  $condition
     */
    public function modalMode(Closure|bool|null $condition = null): static
    {
        $this->isModalMode = \is_null($condition) || value($condition, $this);

        return $this;
    }

    public function isModalMode(): bool
    {
        return $this->isModalMode;
    }

    public function getModalButton(
        Components $components,
        string $label,
        string $fragmentName
    ): ActionButtonContract {
        return ActionButton::make($label)->inModal(
            title: $label,
            content: (string) Fragment::make($components)->name($fragmentName)
        );
    }
}