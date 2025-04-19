<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core;

/**
 * @internal
 * @template TData
 *
 */
interface CrudResourceWithResponseModifiersContract
{
    /**
     * @param TData $item
     */
    public function modifyResponse(mixed $item): mixed;

    /**
     * @param  iterable<TData>  $items
     */
    public function modifyCollectionResponse(iterable $items): mixed;
}
