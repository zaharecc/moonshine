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

    public function getResources(string $namespace): array
    {
        return $this->resources ??= $this->getDetected($namespace);
    }

    public function getFilename(): string
    {
        return base_path('bootstrap/cache/moonshine.php');
    }

    protected function getDetected(string $namespace): array
    {
        if (file_exists($path = $this->getFilename())) {
            return require $path;
        }

        return $this->getPrepared(
            $this->getMerged(
                $this->getPages(),
                $this->getFiltered($namespace)
            )
        );
    }

    protected function getMerged(array $pages, array $autoload): array
    {
        if (! $pages) {
            return $autoload;
        }

        $autoload['pages'] = array_unique(array_merge($pages, $autoload['pages'] ?? []));

        return $autoload;
    }

    protected function getPrepared(array $items): array
    {
        foreach ($items as &$values) {
            $values = Collection::make($values)->map(
                static fn (string $class) => Str::start($class, '\\')
            )->all();
        }

        return $items;
    }

    protected function getPages(): array
    {
        // @phpstan-ignore-next-line
        return $this->config->getPages();
    }

    protected function getFiltered(string $namespace): array
    {
        return Collection::make(ClassLoader::getRegisteredLoaders())
            ->map(
                fn (ClassLoader $loader) => Collection::make($loader->getClassMap())
                    ->filter(static fn (string $path, string $class) => str_starts_with($class, $namespace))
                    ->flip()
                    ->values()
                    ->filter(function (string $class) {
                        return $this->isInstanceOf($class, [PageContract::class, ResourceContract::class])
                            && $this->isNotAbstract($class);
                    })
            )
            ->collapse()
            ->groupBy(fn (string $class) => $this->getGroupName($class))
            ->toArray();
    }

    protected function getGroupName(string $class): string
    {
        if ($this->isInstanceOf($class, PageContract::class)) {
            return 'pages';
        }

        return 'resources';
    }

    protected function isInstanceOf(string $haystack, array|string $needles): bool
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
