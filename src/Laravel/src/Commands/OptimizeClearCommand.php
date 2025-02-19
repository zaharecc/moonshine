<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Filesystem\Filesystem;
use MoonShine\Core\Collections\AutoloadCollection;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\info;

#[AsCommand(name: 'moonshine:optimize-clear')]
class OptimizeClearCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:optimize-clear';

    protected $description = 'Remove the cached bootstrap files';

    public function handle(AutoloadCollection $autoload, Filesystem $files): int
    {
        $this->components->info('Clearing cached moonshine file.');

        if ($files->exists($file = $autoload->getFilename())) {
            $files->delete($file);

            info('MoonShine\'s cache has been cleared.');
        }

        return self::SUCCESS;
    }
}
