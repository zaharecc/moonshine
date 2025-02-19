<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Filesystem\Filesystem;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\ResourceContract;
use MoonShine\Core\Collections\AutoloadCollection;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:optimize')]
class OptimizeCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:optimize';

    protected $description = 'Cache MoonShine pages and resources to increase performance';

    /** @var array<string, list<class-string<PageContract|ResourceContract>>>|null */
    protected ?array $sources = null;

    public function handle(AutoloadCollection $autoload, Filesystem $storage): int
    {
        $this->components->info('Caching MoonShine pages and resources.');

        $this->components->task('Search', fn () => $this->search($autoload));
        $this->components->task('Storing', fn () => $this->store($storage, $autoload->getFilename()));

        return self::SUCCESS;
    }

    protected function search(AutoloadCollection $autoload): void
    {
        $this->sources = $autoload->getResources($this->getNamespace(), false);
    }

    protected function store(Filesystem $storage, string $cachePath): void
    {
        $storage->put(
            $cachePath, '<?php return '.var_export($this->sources, true).';'.PHP_EOL
        );
    }
}
