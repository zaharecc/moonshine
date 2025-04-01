<?php

declare(strict_types=1);

namespace MoonShine\Tests\Fixtures\Resources\Crud;

use Closure;
use Generator;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataCasterContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Exceptions\MoonShineNotFoundException;
use MoonShine\Laravel\Resources\CrudResource;
use MoonShine\Tests\Fixtures\TypeCasts\ArrayDataCaster;
use Throwable;

abstract class AbstractTestingCrudResource extends CrudResource
{
    public static int $id = 0;

    /**
     * @var array<int, mixed>
     */
    public static array $items = [];

    public function getCaster(): DataCasterContract
    {
        return new ArrayDataCaster('id');
    }

    public function massDelete(array $ids): void
    {
        foreach ($ids as $id) {
            $this->delete(['id' => $id]);
        }
    }

    public function delete(mixed $item, ?FieldsContract $fields = null): bool
    {
        if (array_key_exists($item['id'], static::$items)) {
            unset(static::$items[$item['id']]);

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
                ...$field->apply($this->applyField($field), $data)
            ];
        }

        if ($item['id'] ?? false) {
            static::$items[$item['id']] = [
                'id' => $item['id'],
                ...$data,
            ];

            $this->isRecentlyCreated = false;

            return $data;
        }

        $this->isRecentlyCreated = true;

        $data = [
            'id' => ++static::$id,
            ...$data,
        ];

        static::$items[$data['id']] = $data;

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
        yield from static::$items;
    }

    public function findItem(bool $orFail = false): mixed
    {
        if (array_key_exists($this->getItemID(), static::$items)) {
            return static::$items[$this->getItemID()];
        }

        if ($orFail) {
            throw new MoonShineNotFoundException();
        }

        return null;
    }

    public static function flushTestItems(): void
    {
        static::$id = 0;
        static::$items = [];
    }

    public static function registerToMoonshine(): void
    {
        static::flushTestItems();

        moonshine()->resources([static::class]);
    }
}
