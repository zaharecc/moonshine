<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use MoonShine\Laravel\Contracts\Notifications\NotificationButtonContract;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Icon;

final class DatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $message,
        protected ?NotificationButtonContract $button = null,
        protected null|string|Color $color = null,
        protected null|string|Icon $icon = null
    ) {
        $this->color = $this->color instanceof Color ? $this->color->value : $this->color;
        $this->icon = $this->icon instanceof Icon ? $this->icon->icon : $this->icon;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }


    /**
     * @return array{message: string, button: array, color: ?string}
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'button' => \is_null($this->button) ? [] : [
                'label' => $this->button->getLabel(),
                'link' => $this->button->getLink(),
            ],
            'color' => $this->color,
            'icon' => $this->icon
        ];
    }
}
