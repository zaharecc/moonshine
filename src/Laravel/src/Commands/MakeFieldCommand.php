<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\{outro, select, text};

use MoonShine\UI\Fields\Field;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(name: 'moonshine:field')]
class MakeFieldCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:field {className?}';

    protected $description = 'Create field';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $className = $this->argument('className') ?? text(
            'Class name',
            'CustomField',
            required: true
        );

        $suggestView = str($className)
            ->classBasename()
            ->kebab()
            ->prepend("admin.fields.")
            ->value();

        $view = text(
            'Path to view',
            $suggestView,
            default: $suggestView,
            required: true
        );

        $extends = select(
            'Extends',
            collect(File::files(__DIR__ . '/../../../UI/src/Fields/'))
                ->mapWithKeys(
                    static fn (SplFileInfo $file): array => [
                        $file->getFilenameWithoutExtension() => $file->getFilenameWithoutExtension(),
                    ]
                )
                ->except(['Field', 'Fields', 'FormElement'])
                ->mapWithKeys(static fn ($file): array => [('MoonShine\UI\Fields\\' . $file) => $file])
                ->prepend('Base', Field::class)
                ->toArray(),
            Field::class
        );

        $fieldsDir = $this->getDirectory('/Fields');
        $fieldPath = "$fieldsDir/$className.php";

        $this->makeDir($fieldsDir);

        $this->copyStub('Field', $fieldPath, [
            '{namespace}' => moonshineConfig()->getNamespace('\Fields'),
            '{view}' => $view,
            '{extend}' => $extends,
            '{extendShort}' => class_basename($extends),
            'DummyField' => $className,
        ]);

        $view = str_replace('.blade.php', '', $view);
        $viewPath = resource_path('views/' . str_replace('.', DIRECTORY_SEPARATOR, $view));
        $viewPath .= '.blade.php';

        $this->makeDir(
            \dirname($viewPath)
        );

        $this->copyStub('view', $viewPath);

        outro(
            "$className was created: " . $this->getRelativePath($fieldPath)
        );

        outro(
            "View was created: " . $this->getRelativePath($viewPath)
        );

        return self::SUCCESS;
    }
}
