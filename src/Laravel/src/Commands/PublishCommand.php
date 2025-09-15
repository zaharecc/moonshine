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
                'forms' => 'System Forms (LoginFrom, FiltersForm)',
                'pages' => 'System Pages (ProfilePage, LoginPage, ErrorPage)',
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
            $this->publishAssetsTemplate();
        }

        if (\in_array('resources', $types, true)) {
            $this->publishResources();
        }

        if (\in_array('forms', $types, true)) {
            $this->publishForms();
        }

        if (\in_array('pages', $types, true)) {
            $this->publishPages();
        }

        return self::SUCCESS;
    }

    private function publishAssetsTemplate(): void
    {
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

            self::updateNodePackages(static fn ($packages): array => [
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

        info('app.css, postcss/tailwind.config published');
        info("Don't forget to add styles to the Layout (Css::make(`Vite::asset('resources/css/app.css')`))");
    }

    private function publishResources(): void
    {
        $this->publishSystemResource('MoonShineUserResource', 'MoonshineUser');
        $this->publishSystemResource('MoonShineUserRoleResource', 'MoonshineUserRole');

        info('Resources published');
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
            self::addResourceOrPageToProviderFile($name, namespace: $targetNamespace);
        }
    }

    private function publishForms(): void
    {
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

    private function publishSystemForm(string $className, string $configKey): void
    {
        $this->makeDir($this->getDirectory('/Forms'));

        $this->copySystemClass($className, 'Forms');

        $this->replaceInConfig(
            $configKey,
            $this->getNamespace('\Forms\\' . $className) . "::class",
            $className
        );
    }

    private function publishPages(): void
    {
        $pageTypes = multiselect(
            'Pages',
            [
                'profile' => 'ProfilePage',
                'login' => 'LoginPage',
                'error' => 'ErrorPage',
            ],
            required: true
        );

        if (\in_array('profile', $pageTypes, true)) {
            $this->publishSystemPage('ProfilePage', 'profile');
        }

        if (\in_array('login', $pageTypes, true)) {
            $this->publishSystemPage('LoginPage', 'login');
        }

        if (\in_array('error', $pageTypes, true)) {
            $this->publishSystemPage('ErrorPage', 'error');
        }

        info('Pages published');
    }

    private function publishSystemPage(string $className, string $configKey): void
    {
        $this->makeDir($this->getDirectory('/Pages'));

        $copyInfo = $this->copySystemClass($className, 'Pages');

        $this->replaceInFile(
            "namespace {$copyInfo['target_namespace']};\n",
            "namespace {$copyInfo['target_namespace']};\n\nuse MoonShine\Laravel\Pages\Page;",
            $copyInfo['full_class_path']
        );

        $this->replaceInConfig(
            $configKey,
            $this->getNamespace('\Pages\\' . $className) . "::class",
            $className
        );
    }

    /**
     * @return array{full_class_path: string, target_namespace: string}
     */
    private function copySystemClass(string $name, string $dir): array
    {
        $classPath = "src/$dir/$name.php";
        $fullClassPath = $this->getDirectory("/$dir/$name.php");
        $targetNamespace = $this->getNamespace("\\$dir");

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
