<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Notifications;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MoonShine\Support\Enums\Color;

/**
 * @implements MoonShineNotificationContract<NotificationMemoryItem>
 */
final class MoonShineMemoryNotification implements MoonShineNotificationContract
{
    private array $messages = [];

    /**
     * @param  array{}|array{'link': string, 'label': string}  $button
     * @param  array<int|string>  $ids
     */
    public static function send(
        string $message,
        array $button = [],
        array $ids = [],
        string|Color|null $color = null,
    ): void {
        (new self())->notify($message, $button, $ids, $color);
    }

    /**
     * @param  array{}|array{'link': string, 'label': string}  $button
     * @param  array<int|string>  $ids
     */
    public function notify(
        string $message,
        array $button = [],
        array $ids = [],
        string|Color|null $color = null,
    ): void {
        if (! moonshineConfig()->isUseNotifications()) {
            return;
        }

        $color = $color instanceof Color ? $color->value : $color;
        $id = (string)Str::uuid();

        $this->messages[$id] = new NotificationMemoryItem(
            id: $id,
            message: $message,
            color: $color,
            date: now(),
            button: $button,
        );
    }

    /**
     * @return Collection<int, NotificationMemoryItem>
     */
    public function getAll(): Collection
    {
        return collect($this->messages);
    }

    public function readAll(): void
    {
        $this->messages = [];
    }

    public function markAsRead(int|string $id): void
    {
        data_forget($this->messages, $id);
    }

    public function getReadAllRoute(): string
    {
        return route('moonshine.notifications.readAll');
    }
}
