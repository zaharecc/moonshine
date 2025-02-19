<?php

declare(strict_types=1);

namespace MoonShine\Core\Collections;

use Composer\Autoload\ClassLoader;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\ResourceContract;
use ReflectionClass;

final class AutoloadCollection
{
    protected ?array $resources = null;

    public function __construct(
        protected ConfiguratorContract $config,
    ) {}

    public function all(string $namespace): array
    {
        return $this->resources ??= $this->detect($namespace);
    }

    public function file(): string
    {
        return base_path('bootstrap/cache/moonshine.php');
    }

    protected function detect(string $namespace): array
    {
        if (file_exists($path = $this->file())) {
            return require $path;
        }

        return $this->prepare(
            $this->merge(
                $this->pages(),
                $this->search($namespace)
            )
        );
    }

    protected function merge(array $pages, array $autoload): array
    {
        if (! $pages) {
            return $autoload;
        }

        $autoload['pages'] = array_unique(array_merge($pages, $autoload['pages'] ?? []));

        return $autoload;
    }

    protected function prepare(array $items): array
    {
        foreach ($items as &$values) {
            $values = Collection::make($values)->map(
                static fn (string $class) => Str::start($class, '\\')
            )->all();
        }

        return $items;
    }

    protected function pages(): array
    {
        // @phpstan-ignore-next-line
        return $this->config->getPages();
    }

    protected function search(string $namespace): array
    {
        return Collection::make(ClassLoader::getRegisteredLoaders())
            ->map(
                fn (ClassLoader $loader) => Collection::make($loader->getClassMap())
                    ->filter(static fn (string $path, string $class) => str_starts_with($class, $namespace))
                    ->flip()
                    ->values()
                    ->filter(function (string $class) {
                        return $this->instanceOf($class, [PageContract::class, ResourceContract::class])
                            && $this->isNotAbstract($class);
                    })
            )
            ->collapse()
            ->groupBy(fn (string $class) => $this->groupBy($class))
            ->toArray();
    }

    protected function groupBy(string $class): string
    {
        if ($this->instanceOf($class, PageContract::class)) {
            return 'pages';
        }

        return 'resources';
    }

    protected function instanceOf(string $haystack, array|string $needles): bool
    {
        $needles = is_array($needles) ? $needles : [$needles];

        foreach ($needles as $needle) {
            if (is_a($haystack, $needle, true)) {
                return true;
            }
        }

        return false;
    }

    protected function isNotAbstract(string $class): bool
    {
        return ! (new ReflectionClass($class))->isAbstract();
    }
}
