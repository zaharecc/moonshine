<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Pages;

use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Forms\LoginForm;
use MoonShine\Laravel\Layouts\LoginLayout;

/**
 * @extends Page<null>
 */
class LoginPage extends Page
{
    protected ?string $layout = LoginLayout::class;

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            moonshineConfig()->getForm('login', LoginForm::class),
        ];
    }
}
