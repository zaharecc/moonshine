<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Notifications;

use MoonShine\Laravel\Contracts\Notifications\NotificationButtonContract;

final readonly class NotificationButton implements NotificationButtonContract
{
    public function __construct(
        private string $label,
        private string $link,
    ) {

    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
