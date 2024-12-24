<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\{confirm, outro, text};

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:layout')]
class MakeLayoutCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:layout {className?} {--compact} {--full} {--default} {--dir=}';

    protected $description = 'Create layout';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $className = $this->argument('className') ?? text(
            'Class name',
            required: true
        );

        $dir = $this->option('dir') ?: \dirname($className);
        $className = class_basename($className);

        if ($dir === '.') {
            $dir = 'Layouts';
        }

        $layoutsDir = $this->getDirectory() . "/$dir";
        $layoutPath = "$layoutsDir/$className.php";

        $this->makeDir($layoutsDir);

        $compact = ! $this->option('full') && ($this->option('compact') || confirm('Want to use a minimalist theme?'));

        $extendClassName = $compact ? 'CompactLayout' : 'AppLayout';
        $extends = "MoonShine\Laravel\Layouts\\$extendClassName";

        $this->copyStub('Layout', $layoutPath, [
            '{namespace}' => moonshineConfig()->getNamespace('\\' . str_replace('/', '\\', $dir)),
            '{extend}' => $extends,
            '{extendShort}' => class_basename($extends),
            'DummyLayout' => $className,
        ]);

        outro(
            "$className was created: " . $this->getRelativePath($layoutPath)
        );

        if ($this->option('default') || confirm('Use the default template in the system?')) {
            $this->replaceInConfig(
                'layout',
                moonshineConfig()->getNamespace('\Layouts\\' . $className) . "::class"
            );
        }

        return self::SUCCESS;
    }
}
