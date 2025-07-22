<?php

declare(strict_types=1);

namespace MoonShine\UI\Fields;

use Illuminate\Support\Collection;
use JsonException;
use MoonShine\Contracts\UI\HasAsyncContract;
use MoonShine\UI\Contracts\DefaultValueTypes\CanBeArray;
use MoonShine\UI\Contracts\DefaultValueTypes\CanBeNumeric;
use MoonShine\UI\Contracts\DefaultValueTypes\CanBeString;
use MoonShine\UI\Contracts\HasDefaultValueContract;
use MoonShine\UI\Contracts\HasUpdateOnPreviewContract;
use MoonShine\UI\Traits\Fields\CanBeMultiple;
use MoonShine\UI\Traits\Fields\HasPlaceholder;
use MoonShine\UI\Traits\Fields\Searchable;
use MoonShine\UI\Traits\Fields\SelectTrait;
use MoonShine\UI\Traits\Fields\UpdateOnPreview;
use MoonShine\UI\Traits\Fields\WithDefaultValue;
use MoonShine\UI\Traits\HasAsync;

class Select extends Field implements
    HasDefaultValueContract,
    CanBeArray,
    CanBeString,
    CanBeNumeric,
    HasUpdateOnPreviewContract,
    HasAsyncContract
{
    use CanBeMultiple;
    use Searchable;
    use SelectTrait;
    use WithDefaultValue;
    use HasAsync;
    use UpdateOnPreview;
    use HasPlaceholder;

    protected string $view = 'moonshine::fields.select';

    protected function resolveRawValue(): mixed
    {
        return $this->resolvePreview();
    }

    /**
     * @throws JsonException
     */
    protected function resolvePreview(): string
    {
        $value = $this->toValue();

        if ($this->isMultiple()) {
            $value = \is_string($value) && str($value)->isJson() ?
                json_decode($value, true, 512, JSON_THROW_ON_ERROR)
                : $value;

            return collect($value)
                ->when(
                    ! $this->isRawMode(),
                    fn ($collect): Collection => $collect->map(
                        fn ($v): string => (string) data_get($this->getValues()->flatten(), "$v.label", ''),
                    ),
                )
                ->implode(',');
        }

        if (\is_null($value)) {
            return '';
        }

        return (string) data_get($this->getValues()->flatten(), "$value.label", '');
    }

    public function asyncOnInit(bool $whenOpen = true): static
    {
        return $this->customAttributes([
            'data-async-on-init' => true,
            'data-async-on-init-dropdown' => $whenOpen,
        ]);
    }

    protected function asyncWith(): void
    {
        $this->searchable();
    }

    public function prepareReactivityValue(mixed $value, mixed &$casted, array &$except): mixed
    {
        $result = data_get($value, 'value', $value);

        return $this->isMultiple() && \is_array($result)
            ? array_filter($result, static fn ($value): bool => $value !== null && $value !== false)
            : $result;
    }

    protected function prepareBeforeRender(): void
    {
        parent::prepareBeforeRender();

        if (! $this->getAttributes()->has('data-validation-field')) {
            $this->customAttributes([
                'data-validation-field' => preg_replace("/\[\d*]$/", '', $this->getNameAttribute()),
            ]);
        }
    }

    protected function viewData(): array
    {
        return [
            'isSearchable' => $this->isSearchable(),
            'asyncUrl' => $this->getAsyncUrl(),
            'values' => $this->getValues()->toArray(),
            'isNullable' => $this->isNullable(),
            'isNative' => $this->isNative(),
        ];
    }
}
