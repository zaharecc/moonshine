<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\{outro, suggest};

use MoonShine\Laravel\MoonShineAuth;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'moonshine:policy')]
class MakePolicyCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:policy {className?}';

    protected $description = 'Create policy for Model';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $modelPath = is_dir(app_path('Models')) ? app_path('Models') : app_path();

        $className = $this->argument('className') ?? suggest(
            label: 'Model',
            options: collect((new Finder())->files()->depth(0)->in($modelPath))
                ->map(static fn ($file) => $file->getBasename('.php'))
                ->values()
                ->all(),
            required: true,
        );

        $className = str($className)
            ->ucfirst()
            ->remove('policy', false)
            ->value();

        $model = $this->qualifyModel($className);
        $className = class_basename($model) . 'Policy';

        $policiesDir = app_path('/Policies');
        $policyPath = "$policiesDir/$className.php";

        $this->makeDir($policiesDir);

        $this->copyStub('Policy', $policyPath, [
            'DummyClass' => $className,
            '{model-namespace}' => $model,
            '{model}' => class_basename($model),
            '{user-model-namespace}' => MoonShineAuth::getModel()::class,
            '{user-model}' => class_basename(MoonShineAuth::getModel()),
        ]);

        outro(
            "$className was created: " . $this->getRelativePath($policyPath)
        );

        return self::SUCCESS;
    }
}
