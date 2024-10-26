<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Notifications;

use DateTimeInterface;

final readonly class NotificationMemoryItem implements NotificationItemContract
{
    public function __construct(
        private null|int|string $id,
        private ?string $message,
        private ?string $color = null,
        private ?DateTimeInterface $date = null,
        private array $button = [],
    ) {
    }

    public function getId(): int|string|null
    {
        return $this->id;
    }

    public function getReadRoute(): string
    {
        return route('moonshine.notifications.read', $this->getId());
    }

    public function getColor(): string
    {
        return $this->color ?? 'green';
    }

    public function getMessage(): string
    {
        return $this->message ?? '';
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date ?? now();
    }

    public function getButton(): array
    {
        return $this->button ?? [];
    }

    public function getButtonLink(): ?string
    {
        return data_get($this->getButton(), 'link');
    }

    public function getButtonLabel(): ?string
    {
        return data_get($this->getButton(), 'label');
    }

    public function getIcon(): string
    {
        return 'information-circle';
    }
}
