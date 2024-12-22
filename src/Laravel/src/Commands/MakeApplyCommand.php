<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\{outro, text};

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:apply')]
class MakeApplyCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:apply {className?}';

    protected $description = 'Create apply for Field';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $className = $this->argument('className') ?? text(
            'Class name',
            required: true
        );

        $appliesDir = $this->getDirectory('/Applies');
        $apply = "$appliesDir/$className.php";

        $this->makeDir($appliesDir);

        $this->copyStub('Apply', $apply, [
            '{namespace}' => moonshineConfig()->getNamespace('\Applies'),
            'DummyClass' => $className,
        ]);

        outro(
            "$className was created: " . $this->getRelativePath($apply)
        );

        return self::SUCCESS;
    }
}
