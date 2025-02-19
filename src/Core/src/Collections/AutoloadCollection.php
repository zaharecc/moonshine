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
    /** @var array<string, list<class-string<PageContract|ResourceContract>>>|null */
    protected ?array $resources = null;

    public function __construct(
        protected ConfiguratorContract $config,
    ) {}

    /**
     * @param  string  $namespace
     * @param  bool  $withCache
     *
     * @return array<string, list<class-string<PageContract|ResourceContract>>>
     */
    public function getResources(string $namespace, bool $withCache = true): array
    {
        return $this->resources ??= $this->getDetected($namespace, $withCache);
    }

    public function getFilename(): string
    {
        return base_path('bootstrap/cache/moonshine.php');
    }

    /**
     * @param  string  $namespace
     * @param  bool  $withCache
     * @return array<string, list<class-string<PageContract|ResourceContract>>>
     */
    protected function getDetected(string $namespace, bool $withCache): array
    {
        if ($withCache && file_exists($path = $this->getFilename())) {
            return require $path;
        }

        return $this->getPrepared(
            $this->getMerged(
                $this->getPages(),
                $this->getFiltered($namespace)
            )
        );
    }

    /**
     * @param  list<class-string<PageContract>>  $pages
     * @param  array<string, list<class-string<PageContract|ResourceContract>>>  $autoload
     * @return array<string, list<class-string<PageContract|ResourceContract>>>
     */
    protected function getMerged(array $pages, array $autoload): array
    {
        if (! $pages) {
            return $autoload;
        }

        $autoload['pages'] = array_unique(array_merge($pages, $autoload['pages'] ?? []));

        return $autoload;
    }

    /**
     * @param  array<string, list<class-string<PageContract|ResourceContract>>>  $items
     * @return array<string, list<class-string<PageContract|ResourceContract>>>
     */
    protected function getPrepared(array $items): array
    {
        foreach ($items as &$values) {
            $values = Collection::make($values)->map(
                static fn (string $class) => Str::start($class, '\\')
            )->all();
        }

        return $items;
    }

    /**
     * @return array<class-string<PageContract>>
     */
    protected function getPages(): array
    {
        return $this->config->getPages();
    }

    /**
     * @param  string  $namespace
     * @return array<string, list<class-string<PageContract|ResourceContract>>>
     */
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

    /**
     * @param  class-string<PageContract|ResourceContract>  $class
     * @return string
     */
    protected function getGroupName(string $class): string
    {
        if ($this->isInstanceOf($class, PageContract::class)) {
            return 'pages';
        }

        return 'resources';
    }

    /**
     * @param  class-string  $haystack
     * @param  list<class-string>|string  $needles
     * @return bool
     */
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

    /**
     * @param  class-string<PageContract|ResourceContract>  $class
     * @throws \ReflectionException
     * @return bool
     */
    protected function isNotAbstract(string $class): bool
    {
        return ! (new ReflectionClass($class))->isAbstract();
    }
}
