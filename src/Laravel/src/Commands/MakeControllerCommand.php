<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\{outro, text};

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:controller')]
class MakeControllerCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:controller {name?}';

    protected $description = 'Create controller';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $name = $this->argument('name') ?? text(
            'Class name',
            required: true
        );

        $controllersDir = $this->getDirectory('/Controllers');
        $controllerPath = "$controllersDir/$name.php";

        $this->makeDir($controllersDir);

        $this->copyStub('Controller', $controllerPath, [
            '{namespace}' => moonshineConfig()->getNamespace('\Controllers'),
            'DummyClass' => $name,
        ]);

        outro(
            "$name was created: " . $this->getRelativePath($controllerPath)
        );

        return self::SUCCESS;
    }
}
