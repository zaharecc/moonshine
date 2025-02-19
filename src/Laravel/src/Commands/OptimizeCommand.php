<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use MoonShine\Core\Collections\AutoloadCollection;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:optimize')]
class OptimizeCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:optimize';

    protected $description = 'Cache MoonShine pages and resources to increase performance';

    protected ?array $sources = null;

    public function handle(AutoloadCollection $autoload): int
    {
        $this->components->info('Caching MoonShine pages and resources.');

        $this->components->task('Search', fn () => $this->search($autoload));
        $this->components->task('Storing', fn () => $this->store($autoload->file()));

        return self::SUCCESS;
    }

    protected function search(AutoloadCollection $autoload): void
    {
        $this->sources = $autoload->all($this->getNamespace());
    }

    protected function store(string $filename): void
    {
        file_put_contents(
            $filename,
            sprintf("<?php\n\nreturn %s;", var_export($this->sources, true))
        );
    }
}
