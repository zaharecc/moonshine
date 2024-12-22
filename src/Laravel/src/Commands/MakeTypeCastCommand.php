<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\{outro, text};

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:type-cast')]
class MakeTypeCastCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:type-cast {className?}';

    protected $description = 'Create type cast class';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $className = $this->argument('className') ?? text(
            'Class name',
            required: true
        );

        $typeCastsDir = $this->getDirectory('/TypeCasts');
        $typeCastPath = "$typeCastsDir/$className.php";

        $this->makeDir($typeCastsDir);

        $this->copyStub('TypeCast', $typeCastPath, [
            '{namespace}' => moonshineConfig()->getNamespace('\TypeCasts'),
            'DummyCast' => $className,
        ]);

        outro(
            "$className was created: " . $this->getRelativePath($typeCastPath)
        );

        return self::SUCCESS;
    }
}
