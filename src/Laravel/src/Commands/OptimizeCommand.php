<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Filesystem\Filesystem;
use LogicException;
use MoonShine\Contracts\Core\DependencyInjection\OptimizerCollectionContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\ResourceContract;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

#[AsCommand(name: 'moonshine:optimize')]
class OptimizeCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:optimize';

    protected $description = 'Cache MoonShine pages and resources to increase performance';

    public function handle(OptimizerCollectionContract $optimizer, Filesystem $files): int
    {
        $this->components->info('Caching MoonShine pages and resources.');

        $filename = $optimizer->getCachePath();

        $this->store($files, $filename, $this->getFreshSources($optimizer));

        $this->validateCache($files, $filename);

        $this->components->info('MoonShine cached successfully.');

        return self::SUCCESS;
    }

    /**
     * @param  OptimizerCollectionContract  $optimizer
     *
     * @return array<string, list<class-string<PageContract|ResourceContract>>>
     */
    protected function getFreshSources(OptimizerCollectionContract $optimizer): array
    {
        return $optimizer->getTypes($this->getNamespace(), false);
    }

    /**
     * @param  \Illuminate\Filesystem\Filesystem  $storage
     * @param  string  $cachePath
     * @param  array<string, list<class-string<PageContract|ResourceContract>>>  $sources
     *
     * @return void
     */
    protected function store(Filesystem $storage, string $cachePath, array $sources): void
    {
        $storage->put(
            $cachePath,
            '<?php return ' . var_export($sources, true) . ';' . PHP_EOL
        );
    }

    protected function validateCache(Filesystem $files, string $filename): void
    {
        try {
            require $filename;
        }
        catch (Throwable $e) {
            $files->delete($filename);

            throw new LogicException('Your MoonShine file are not serializable.', 0, $e);
        }
    }
}
