<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Contracts\Notifications;

use MoonShine\Contracts\UI\ComponentAttributesBagContract;

interface NotificationButtonContract
{
    public function getLink(): string;

    public function getLabel(): string;

    public function getAttributes(): ComponentAttributesBagContract;
}
