<?php

declare(strict_types=1);

namespace MoonShine\Contracts\UI;

/**
 * @template TComponent of ComponentContract
 */
interface FieldWithComponent
{
    /**
     * @return TComponent
     */
    public function getComponent(): ComponentContract;
}
