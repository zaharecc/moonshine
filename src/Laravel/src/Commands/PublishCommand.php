<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Illuminate\Filesystem\Filesystem;

use function Laravel\Prompts\{confirm, info, multiselect};

use MoonShine\Laravel\DependencyInjection\MoonShine;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'moonshine:publish')]
class PublishCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:publish {type?}';

    public function handle(): int
    {
        $types = $this->argument('type') ? [$this->argument('type')] : multiselect(
            'Types',
            [
                'assets' => 'Assets',
                'assets-template' => 'Assets template',
                'resources' => 'System Resources (MoonShineUserResource, MoonShineUserRoleResource)',
                'forms' => 'Forms'
            ],
            required: true
        );

        if (\in_array('assets', $types, true)) {
            $this->call('vendor:publish', [
                '--tag' => 'moonshine-assets',
                '--force' => true,
            ]);
        }

        if (\in_array('assets-template', $types, true)) {
            $this->copyStub(
                'assets/css',
                resource_path('css/app.css')
            );

            $this->copyStub(
                'assets/postcss.config.preset',
                base_path('postcss.config.js')
            );

            $this->copyStub(
                'assets/tailwind.config.preset',
                base_path('tailwind.config.js')
            );

            if (confirm('Install modules automatically? (tailwindcss, autoprefixer, postcss)')) {
                $this->flushNodeModules();

                self::updateNodePackages(static fn ($packages) => [
                        '@tailwindcss/typography' => '^0.5',
                        '@tailwindcss/line-clamp' => '^0.4',
                        '@tailwindcss/aspect-ratio' => '^0.4',
                        'tailwindcss' => '^3',
                        'autoprefixer' => '^10',
                        'postcss' => '^8',
                    ] + $packages);

                $this->installNodePackages();

                info('Node packages installed');
            }

            info('App.css, postcss/tailwind.config published');
            info("Don't forget to add to MoonShineServiceProvider `Vite::asset('resources/css/app.css')`");
        }

        if (\in_array('resources', $types, true)) {
            $this->publishSystemResource('MoonShineUserResource', 'MoonshineUser');
            $this->publishSystemResource('MoonShineUserRoleResource', 'MoonshineUserRole');

            info('Resources published');
        }

        if (\in_array('forms', $types, true)) {

            $formTypes = multiselect(
                'Forms',
                [
                    'login' => 'LoginForm',
                    'filters' => 'FiltersForm',
                ],
                required: true
            );

            if (\in_array('login', $formTypes, true)) {
                $this->publishSystemForm('LoginForm', 'login');
            }

            if (\in_array('filters', $formTypes, true)) {
                $this->publishSystemForm('FiltersForm', 'filters');
            }

            info('Forms published');
        }

        return self::SUCCESS;
    }

    private function publishSystemResource(string $name, string $model): void
    {
        $copyInfo = $this->copySystemClass($name, 'Resources');
        $fullClassPath = $copyInfo['full_class_path'];
        $targetNamespace = $copyInfo['target_namespace'];

        $this->replaceInFile(
            "use MoonShine\Laravel\Models\\$model;",
            "use MoonShine\Laravel\Models\\$model;\nuse MoonShine\Laravel\Resources\ModelResource;",
            $fullClassPath
        );

        $this->replaceInFile(
            "use MoonShine\Laravel\Resources\\$name;",
            "use $targetNamespace\\$name;",
            app_path('Providers/MoonShineServiceProvider.php')
        );

        $provider = file_get_contents(app_path('Providers/MoonShineServiceProvider.php'));

        if (! str_contains($provider, "$targetNamespace\\$name")) {
            self::addResourceOrPageToProviderFile($name);
        }
    }

    private function publishSystemForm(string $className, string $configKey): void
    {
        if (! is_dir($this->getDirectory() . "/Forms")) {
            $this->makeDir($this->getDirectory() . "/Forms");
        }

        $this->copySystemClass($className, 'Forms');

        $current = config("moonshine.forms.$configKey", "$className::class");
        $currentShort = class_basename($current);

        $replace = "'$configKey' => " . moonshineConfig()->getNamespace('\Forms\\' . $className) . "::class";

        file_put_contents(
            config_path('moonshine.php'),
            str_replace(
                ["'$configKey' => $current::class", "'$configKey' => $currentShort::class"],
                $replace,
                file_get_contents(config_path('moonshine.php'))
            )
        );
    }

    /**
     * @return array{full_class_path: string, target_namespace: string}
     */
    private function copySystemClass(string $name, string $dir): array
    {
        $classPath = "src/$dir/$name.php";
        $fullClassPath = moonshineConfig()->getDir("/$dir/$name.php");
        $targetNamespace = moonshineConfig()->getNamespace("\\$dir");

        (new Filesystem())->put(
            $fullClassPath,
            file_get_contents(MoonShine::path($classPath))
        );

        $this->replaceInFile(
            "namespace MoonShine\Laravel\\$dir;",
            "namespace $targetNamespace;",
            $fullClassPath
        );

        return [
            'full_class_path' => $fullClassPath,
            'target_namespace' => $targetNamespace,
        ];
    }
}
