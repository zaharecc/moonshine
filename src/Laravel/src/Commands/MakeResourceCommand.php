<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

use function Laravel\Prompts\{info, select, text};

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:resource')]
class MakeResourceCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:resource {name?} {--m|model=} {--t|title=} {--test} {--pest} {--p|policy}';

    protected $description = 'Create resource';

    /**
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $name = $this->argument('name') ?? text(
            'Resource name',
            'ArticleResource',
            required: true,
        );

        $name = str($name)
            ->ucfirst()
            ->remove('resource', false)
            ->value();

        $model = $this->qualifyModel($this->option('model') ?? $name);
        $title = $this->option('title') ?? str($name)->singular()->plural()->value();
        $resourcesDir = $this->getDirectory('/Resources');

        $resource = "$resourcesDir/{$name}Resource.php";

        $this->makeDir($resourcesDir);

        $stub = select('Resource type', [
            'ModelResourceDefault' => 'Default model resource',
            'ModelResourceWithPages' => 'Model resource with pages',
            'Resource' => 'Empty resource',
        ], 'ModelResourceDefault');

        $replace = [
            '{namespace}' => moonshineConfig()->getNamespace('\Resources'),
            '{model-namespace}' => $model,
            '{model}' => class_basename($model),
            'DummyTitle' => $title,
            'Dummy' => $name,
        ];

        if ($this->option('test') || $this->option('pest')) {
            $testStub = $this->option('pest') ? 'pest' : 'test';
            $testPath = base_path("tests/Feature/{$name}ResourceTest.php");

            $this->copyStub($testStub, $testPath, $replace);

            info('Test file was created');
        }

        if ($stub === 'ModelResourceWithPages') {
            $this->call(MakePageCommand::class, [
                'className' => $name,
                '--crud' => true,
                '--without-register' => true,
            ]);

            $pageNamespace = moonshineConfig()->getNamespace("\Pages\\$name\\$name");

            $replace += [
                '{indexPage}' => "{$name}IndexPage",
                '{formPage}' => "{$name}FormPage",
                '{detailPage}' => "{$name}DetailPage",
                '{index-page-namespace}' => "{$pageNamespace}IndexPage",
                '{form-page-namespace}' => "{$pageNamespace}FormPage",
                '{detail-page-namespace}' => "{$pageNamespace}DetailPage",
            ];
        }

        $this->copyStub($stub, $resource, $replace);

        info(
            "{$name}Resource file was created: " . $this->getRelativePath($resource)
        );

        self::addResourceOrPageToProviderFile(
            "{$name}Resource"
        );

        self::addResourceOrPageToMenu(
            "{$name}Resource",
            $title
        );

        if ($this->option('policy')) {
            $this->call(MakePolicyCommand::class, [
                'className' => class_basename($model),
            ]);
        }

        return self::SUCCESS;
    }
}
