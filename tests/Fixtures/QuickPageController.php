<?php

declare(strict_types=1);

namespace MoonShine\Tests\Fixtures;

use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Http\Controllers\MoonShineController;

class QuickPageController extends MoonShineController
{
    public function __invoke(): PageContract
    {
        return $this->view('moonshine-tests::quick-page', [
            'var1' => 'var1',
            'var2' => 'var2',
        ]);
    }
}
