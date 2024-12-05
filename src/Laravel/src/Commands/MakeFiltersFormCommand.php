<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use function Laravel\Prompts\outro;

#[AsCommand(name: 'moonshine:filters-form')]
class MakeFiltersFormCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:filters-form {className?} {--dir=}';

    protected $description = 'Create Filters form';

    public function handle(): int
    {
        $className = $this->argument('className') ?? 'FiltersForm';

        $dir = $this->option('dir') ?: \dirname($className);
        $className = class_basename($className);

        if ($dir === '.') {
            $dir = 'Forms';
        }

        $loginForm = $this->getDirectory() . "/$dir/$className.php";

        if (! is_dir($this->getDirectory() . "/$dir")) {
            $this->makeDir($this->getDirectory() . "/$dir");
        }

        $this->copyStub('FiltersForm', $loginForm, [
            '{namespace}' => moonshineConfig()->getNamespace('\\' . str_replace('/', '\\', $dir)),
            'DummyLayout' => $className,
        ]);

        outro(
            "$className was created: " . str_replace(
                base_path(),
                '',
                $loginForm
            )
        );

        $current = config('moonshine.forms.filters', 'FiltersForm::class');
        $currentShort = class_basename($current);
        $replace = "'filters' => " . moonshineConfig()->getNamespace('\Forms\\' . $className) . "::class";

        file_put_contents(
            config_path('moonshine.php'),
            str_replace([
                "'filters' => $current::class",
                "'filters' => $currentShort::class",
            ], $replace, file_get_contents(config_path('moonshine.php')))
        );

        return self::SUCCESS;
    }
}
