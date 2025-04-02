<?php

declare(strict_types=1);

namespace MoonShine\Tests\Fixtures\Resources\Crud;

use Closure;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataCasterContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Core\TypeCasts\MixedDataCaster;
use MoonShine\Laravel\Exceptions\MoonShineNotFoundException;
use MoonShine\Laravel\Resources\CrudResource;
use Throwable;

abstract class AbstractTestingCrudResource extends CrudResource
{
    private int $lastId = 0;

    /**
     * @var array<int, mixed>
     */
    private array $items = [];

    public function getCaster(): DataCasterContract
    {
        return new MixedDataCaster('id');
    }

    public function getLastId(): int
    {
        return $this->lastId;
    }

    private function newId(): int
    {
        return ++$this->lastId;
    }

    public function massDelete(array $ids): void
    {
        foreach ($ids as $id) {
            $this->delete(['id' => $id]);
        }
    }

    public function delete(mixed $item, ?FieldsContract $fields = null): bool
    {
        if (\array_key_exists($item['id'], $this->items)) {
            unset($this->items[$item['id']]);

            return true;
        }

        return false;
    }

    /**
     * @throws Throwable
     */
    public function save(mixed $item, ?FieldsContract $fields = null): array
    {
        $fields ??= $this->getFormFields();

        $fields->fill($item, $this->getCaster()->cast($item));

        $data = [];

        foreach ($fields as $field) {
            $data = [
                ...$data,
                ...$field->apply($this->applyField($field), $data),
            ];
        }

        if ($item['id'] ?? false) {
            $this->items[$item['id']] = [
                'id' => $item['id'],
                ...$data,
            ];

            $this->isRecentlyCreated = false;

            return $data;
        }

        $this->isRecentlyCreated = true;

        $data = [
            'id' => $this->newId(),
            ...$data,
        ];

        $this->items[$data['id']] = $data;

        return $data;
    }

    private function applyField(FieldContract $field): Closure
    {
        return static function (mixed $item, mixed $value) use ($field): mixed {
            $value = $value !== false
                ? $value
                : null;

            data_set($item, $field->getColumn(), $value);

            return $item;
        };
    }

    public function getItems(): iterable
    {
        yield from $this->items;
    }

    public function findItem(bool $orFail = false): mixed
    {
        if (\array_key_exists($this->getItemID(), $this->items)) {
            return $this->items[$this->getItemID()];
        }

        if ($orFail) {
            throw new MoonShineNotFoundException();
        }

        return null;
    }

    public function flushState(): void
    {
        parent::flushState();

        $this->lastId = 0;
        $this->items = [];
    }
}
