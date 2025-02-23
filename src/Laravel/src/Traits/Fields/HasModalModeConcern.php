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

    protected ?Closure $modifyModalModeButton = null;

    protected ?Closure $modifyModalModeModal = null;

    public function modalMode(
        Closure|bool|null $condition = null,
        ?Closure $modifyButton = null,
        ?Closure $modifyModal = null
    ): static {
        $this->isModalMode = \is_null($condition) || value($condition, $this);

        $this->modifyModalModeButton = $modifyButton;

        $this->modifyModalModeModal = $modifyModal;

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
        $button = ActionButton::make($label)->inModal(
            title: $label,
            content: (string) Fragment::make($components)->name($fragmentName),
            builder: $this->modifyModalModeModal
        );

        if (! \is_null($this->modifyModalModeButton)) {
            $button = value($this->modifyModalModeButton, $button, $this);
        }

        return $button;
    }
}
