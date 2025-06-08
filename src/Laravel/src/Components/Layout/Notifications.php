<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Components\Layout;

use Illuminate\Support\Collection;
use MoonShine\Laravel\Contracts\Notifications\MoonShineNotificationContract;
use MoonShine\UI\Components\MoonShineComponent;

final class Notifications extends MoonShineComponent
{
    protected string $view = 'moonshine::components.layout.notifications';

    protected array $translates = [
        'title' => 'moonshine::ui.notifications.title',
        'mark_as_read' => 'moonshine::ui.notifications.mark_as_read',
        'mark_as_read_all' => 'moonshine::ui.notifications.mark_as_read_all',
    ];

    private readonly MoonShineNotificationContract $notificationService;

    public Collection $notifications;

    public string $readAllRoute = '';

    protected function prepareBeforeRender(): void
    {
        $this->notificationService = $this
            ->getCore()
            ->getContainer(MoonShineNotificationContract::class);

        $this->notifications = $this->notificationService->getAll();
        $this->readAllRoute = $this->notificationService->getReadAllRoute();
    }

    protected function viewData(): array
    {
        return [
            'notifications' => $this->notifications,
            'readAllRoute' => $this->readAllRoute,
        ];
    }
}
