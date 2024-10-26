<?php

declare(strict_types=1);

namespace MoonShine\Support\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final readonly class AsyncCallback implements Arrayable, JsonSerializable
{
    public function __construct(
        /**
         * Replacing the async request response handler
         */
        private ?string $responseCallback,

        /**
         * Will be called before the async request
         */
        private ?string $beforeResponse,

        /**
         * Will be called after native request processing if $responseCallback is not specified
         */
        private ?string $afterCallback,
    ) {
    }

    public static function with(
        ?string $responseCallback = null,
        ?string $beforeResponse = null,
        ?string $afterCallback = null
    ): self {
        return new self($responseCallback, $beforeResponse, $afterCallback);
    }

    public function responseCallback(): ?string
    {
        return $this->responseCallback;
    }

    public function beforeResponse(): ?string
    {
        return $this->beforeResponse;
    }

    public function afterCallback(): ?string
    {
        return $this->afterCallback;
    }

    public function toArray(): array
    {
        return [
            'responseCallback' => $this->responseCallback(),
            'beforeResponse' => $this->beforeResponse(),
            'afterCallback' => $this->afterCallback(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
