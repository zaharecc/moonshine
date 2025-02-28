<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Support;

use Attribute;
use Illuminate\Support\Str;
use Leeto\FastAttributes\Attributes;
use MoonShine\Contracts\Core\DependencyInjection\CacheAttributesContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Support\Attributes\SearchUsingFullText;
use Stringable;

/**
 * @internal
 */
final readonly class CacheAttributes implements CacheAttributesContract
{
    /**
     * @param  MoonShine  $core
     */
    public function __construct(private CoreContract $core) {}

    public function get(
        \Closure $default,
        string $target,
        string $attribute,
        ?int $type = null,
        ?string $concrete = null,
        ?array $column = null,
    ): mixed {
        $optimizer = $this->core->getOptimizer();

        if ($this->core->getOptimizer()->hasType(Attribute::class)) {
            $type = $type ?? Attribute::TARGET_CLASS;
            $attributes = $optimizer->getType(Attribute::class);
            $str = Str::of("$target.$type.$attribute")
                ->when(
                    $concrete !== null,
                    fn(Stringable $str) => $str->append(".$concrete"),
                );

            $find = static fn(?string $suffix = null, bool $withDefault = true) => data_get(
                $attributes,
                (string) $str->when(
                    $suffix !== null,
                    fn(Stringable $str) => $str->append(".$suffix"),
                ),
                $withDefault ? $default : null,
            );

            $key = array_key_first($column ?? []);
            $value = reset($column);

            return $column === null
                ? $find()
                : $find((string) $value, false) ?? $find((string) $key);
        }

        return $default();
    }

    public function resolve(): array
    {
        $data = [];

        foreach ($this->core->getResources() as $resource) {
            $classAttributes = Attributes::for($resource)->class()->get();

            foreach ($classAttributes as $attribute) {
                $data[$resource::class][Attribute::TARGET_CLASS][$attribute->getName()] = $attribute->getArguments();
            }

            $search = Attributes::for($resource, SearchUsingFullText::class)->method('search')->first();

            if ($search !== null) {
                $data[$resource::class][Attribute::TARGET_METHOD][$search::class]['search'] = [
                    'columns' => $search->columns,
                    'options' => $search->options,
                ];
            }
        }

        foreach ($this->core->getPages() as $page) {
            $classAttributes = Attributes::for($page)->class()->get();

            foreach ($classAttributes as $attribute) {
                $data[$page::class][Attribute::TARGET_CLASS][$attribute->getName()] = $attribute->getArguments();
            }
        }

        return $data;
    }
}
