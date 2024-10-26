<?php

declare(strict_types=1);

namespace MoonShine\Support\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final readonly class AsyncCallback implements Arrayable, JsonSerializable
{
    public function __construct(
        /**
         * Called before a request
         */
        private ?string $beforeRequest,

        /**
         * Replaces the default response handler
         */
        private ?string $customResponse,

        /**
         * Called after standard response processing if $customResponse is not specified
         */
        private ?string $afterResponse,
    ) {
    }

    public static function with(
        ?string $beforeRequest = null,
        ?string $customResponse = null,
        ?string $afterResponse = null
    ): self {
        return new self($beforeRequest, $customResponse, $afterResponse);
    }

    public function beforeRequest(): ?string
    {
        return $this->beforeRequest;
    }

    public function customResponse(): ?string
    {
        return $this->customResponse;
    }

    public function afterResponse(): ?string
    {
        return $this->afterResponse;
    }

    public function toArray(): array
    {
        return [
            'beforeRequest' => $this->beforeRequest(),
            'customResponse' => $this->customResponse(),
            'afterResponse' => $this->afterResponse(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
