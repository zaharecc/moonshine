<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts\Notifications;

interface NotificationButtonContract
{
    public function getLink(): string;

    public function getLabel(): string;
}
