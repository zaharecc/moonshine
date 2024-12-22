<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\{outro, text};

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:handler')]
class MakeHandlerCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:handler {className?}';

    protected $description = 'Create handler class';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $className = $this->argument('className') ?? text(
            'Class name',
            required: true
        );

        $handlersDir = $this->getDirectory() . '/Handlers';
        $handlerPath = "$handlersDir/$className.php";

        $this->makeDir($handlersDir);

        $this->copyStub('Handler', $handlerPath, [
            '{namespace}' => moonshineConfig()->getNamespace('\Handlers'),
            'DummyHandler' => $className,
        ]);

        outro(
            "$className was created: " . $this->getRelativePath($handlerPath)
        );

        return self::SUCCESS;
    }
}
