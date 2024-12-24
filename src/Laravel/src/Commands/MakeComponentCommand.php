<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\{outro, text};

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:component')]
class MakeComponentCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:component {className?}';

    protected $description = 'Create component';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $className = $this->argument('className') ?? text(
            'Class name',
            required: true
        );

        $suggestView = str($className)
            ->classBasename()
            ->kebab()
            ->prepend("admin.components.")
            ->value();

        $view = text(
            'Path to view',
            $suggestView,
            default: $suggestView,
            required: true
        );

        $componentsDir = $this->getDirectory('/Components');
        $componentPath = "$componentsDir/$className.php";

        $this->makeDir($componentsDir);

        $view = str_replace('.blade.php', '', $view);
        $viewPath = resource_path('views/' . str_replace('.', DIRECTORY_SEPARATOR, $view));
        $viewPath .= '.blade.php';

        $this->makeDir(
            \dirname($viewPath)
        );

        $this->copyStub('view', $viewPath);

        $this->copyStub('Component', $componentPath, [
            '{namespace}' => moonshineConfig()->getNamespace('\Components'),
            '{view}' => $view,
            'DummyClass' => $className,
        ]);

        outro(
            "$className was created: " . $this->getRelativePath($componentPath)
        );

        outro(
            "View was created: " . $this->getRelativePath($viewPath)
        );

        return self::SUCCESS;
    }
}
