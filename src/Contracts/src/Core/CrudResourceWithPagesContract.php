<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core;

use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\Collection\ActionButtonsContract;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @internal
 * @template TData
 * @template-covariant TIndexPage of CrudPageContract
 * @template-covariant TFormPage of CrudPageContract
 * @template-covariant TDetailPage of CrudPageContract
 *
 */
interface CrudResourceWithPagesContract
{
    public function setActivePage(?PageContract $page): void;

    /**
     * @return ?PageContract<TIndexPage>
     */
    public function getIndexPage(): ?PageContract;

    /**
     * @return ?PageContract<TFormPage>
     */
    public function getFormPage(): ?PageContract;

    /**
     * @return ?PageContract<TDetailPage>
     */
    public function getDetailPage(): ?PageContract;

    /**
     * @return ?PageContract<TIndexPage|TDetailPage|TFormPage>
     */
    public function getActivePage(): ?PageContract;


    public function getIndexPageUrl(array $params = [], null|string|array $fragment = null): string;

    /**
     * @param DataWrapperContract<TData>|int|string|null $key
     */
    public function getFormPageUrl(
        DataWrapperContract|int|string|null $key = null,
        array $params = [],
        null|string|array $fragment = null
    ): string;

    /**
     * @param DataWrapperContract<TData>|int|string $key
     */
    public function getDetailPageUrl(
        DataWrapperContract|int|string $key,
        array $params = [],
        null|string|array $fragment = null
    ): string;

    public function isIndexPage(): bool;

    public function isFormPage(): bool;

    public function isDetailPage(): bool;

    public function isCreateFormPage(): bool;

    public function isUpdateFormPage(): bool;

    /**
     * @return list<ComponentContract>
     */
    public function getDetailPageComponents(): array;

    /**
     * @return list<ComponentContract>
     */
    public function getFormPageComponents(): array;

    /**
     * @return list<ComponentContract>
     */
    public function getIndexPageComponents(): array;

    public function modifyFormComponent(ComponentContract $component): ComponentContract;

    public function modifyListComponent(ComponentContract $component): ComponentContract;

    public function modifyDetailComponent(ComponentContract $component): ComponentContract;

    public function getTopButtons(): ActionButtonsContract;

    public function getIndexButtons(): ActionButtonsContract;

    public function getFormButtons(): ActionButtonsContract;

    public function getDetailButtons(): ActionButtonsContract;

    public function getFiltersButton(): ActionButtonContract;
}
