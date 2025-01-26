<?php

declare(strict_types=1);

namespace MoonShine\Support;

use MoonShine\Support\Enums\ToastType;

/**
 * @method static static make(ToastType $type, string $message)
 */
class ToastEventParams extends EventParams
{
    public function __construct(ToastType $type, string $message)
    {
        parent::__construct([
            'type' => $type->value,
            'text' => $message,
        ]);
    }

}
