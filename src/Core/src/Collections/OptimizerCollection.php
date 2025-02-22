<?php

declare(strict_types=1);

namespace MoonShine\Core\Collections;

use Composer\Autoload\ClassLoader;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\OptimizerCollectionContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\ResourceContract;
use ReflectionClass;

final class OptimizerCollection implements OptimizerCollectionContract
{
    /** @var list<class-string>|null */
    protected ?array $sources = null;

    /** @var array<class-string, string> */
    protected array $groups = [
        PageContract::class     => 'pages',
        ResourceContract::class => 'resources',
    ];

    public function __construct(
        protected string $cachePath,
        protected ConfiguratorContract $config,
    ) {}

    /**
     * @param  string|null  $namespace
     * @param  bool  $withCache
     *
     * @return array<list<class-string>>
     */
    public function getSources(?string $namespace = null, bool $withCache = true): array
    {
        return $this->sources ??= $this->getDetected($namespace, $withCache);
    }

    /**
     * @param  class-string  $contract
     * @param  string|null  $namespace
     * @param  bool  $withCache
     *
     * @return list<class-string>
     */
    public function getSource(string $contract, ?string $namespace = null, bool $withCache = true): array
    {
        $group = $this->getGroupNameByContract($contract);

        return $this->getSources($namespace, $withCache)[$group] ?? [];
    }

    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    /**
     * @param  string|null  $namespace
     * @param  bool  $withCache
     *
     * @return array<list<class-string>>
     */
    protected function getDetected(?string $namespace, bool $withCache): array
    {
        if ($withCache && file_exists($path = $this->getCachePath())) {
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
     * @param  array<string, list<class-string>>  $autoload
     *
     * @return array<string, list<class-string>>
     */
    protected function getMerged(array $pages, array $autoload): array
    {
        if (! $pages) {
            return $autoload;
        }

        $pagesName = $this->getGroupNameByContract(PageContract::class);

        $autoload[$pagesName] = array_unique(array_merge($pages, $autoload[$pagesName] ?? []));

        return $autoload;
    }

    /**
     * @param  array<string, list<class-string>>  $items
     *
     * @return array<string, list<class-string>>
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
     * @param  string|null  $namespace
     *
     * @return array<string, list<class-string>>
     */
    protected function getFiltered(?string $namespace): array
    {
        return Collection::make(ClassLoader::getRegisteredLoaders())
            ->map(
                fn (ClassLoader $loader) => Collection::make($loader->getClassMap())
                    ->when($namespace, static fn (Collection $items) => $items->filter(
                        static fn (string $path, string $class) => str_starts_with($class, $namespace)
                    ))
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
     * @param  class-string  $class
     *
     * @return string
     */
    protected function getGroupName(string $class): string
    {
        foreach ($this->groups as $contract => $name) {
            if ($this->isInstanceOf($class, $contract)) {
                return $name;
            }
        }

        return $class;
    }

    /**
     * @param  class-string  $contract
     *
     * @return string
     */
    protected function getGroupNameByContract(string $contract): string
    {
        return $this->groups[$contract] ?? $contract;
    }

    /**
     * @param  class-string  $haystack
     * @param  list<class-string>|string  $needles
     *
     * @return bool
     */
    protected function isInstanceOf(string $haystack, array|string $needles): bool
    {
        foreach (Arr::wrap($needles) as $needle) {
            if (is_a($haystack, $needle, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  class-string  $class
     *
     * @throws \ReflectionException
     * @return bool
     */
    protected function isNotAbstract(string $class): bool
    {
        return ! (new ReflectionClass($class))->isAbstract();
    }
}
