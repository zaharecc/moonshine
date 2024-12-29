<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Closure;
use Illuminate\Support\Stringable;
use Leeto\PackageCommand\Command;
use MoonShine\Laravel\Support\StubsPath;
use function Laravel\Prompts\{text, outro};

abstract class MoonShineCommand extends Command
{
    protected string $stubsDir = __DIR__ . '/../../stubs';

    protected function getDirectory(string $path = ''): string
    {
        return moonshineConfig()->getDir($path);
    }

    protected function getRelativePath(string $path): string
    {
        return str_replace(base_path(), '', $path);
    }

    public static function addResourceOrPageToProviderFile(string $class, bool $page = false, string $namespace = ''): void
    {
        $method = $page ? 'pages' : 'resources';
        $namespace = rtrim($namespace, '\\');

        self::addResourceOrPageTo(
            class: "$namespace\\$class",
            to: app_path('Providers/MoonShineServiceProvider.php'),
            isPage: $page,
            between: static fn (Stringable $content): Stringable => $content->betweenFirst("->$method([", ']'),
            replace: static fn (Stringable $content, Closure $tab): Stringable => $content->append("{$tab()}$class::class,\n{$tab(3)}"),
        );
    }

    public static function addResourceOrPageToMenu(string $class, string $title, bool $page = false, string $namespace = ''): void
    {
        $namespace = rtrim($namespace, '\\');

        self::addResourceOrPageTo(
            class: "$namespace\\$class",
            to: app_path('MoonShine/Layouts/MoonShineLayout.php'),
            isPage: $page,
            between: static fn (Stringable $content): Stringable => $content->betweenFirst("protected function menu(): array", '}'),
            replace: static fn (Stringable $content, Closure $tab): Stringable => $content->replace("];", "{$tab()}MenuItem::make('$title', $class::class),\n{$tab(2)}];"),
        );
    }

    /**
     * @param  Closure(Stringable $content): Stringable  $between
     * @param  Closure(Stringable $content, Closure $tab): Stringable  $replace
     */
    private static function addResourceOrPageTo(string $class, string $to, Closure $between, Closure $replace, string $use = ''): void
    {
        if (! file_exists($to)) {
            return;
        }

        $basename = class_basename($class);
        $namespace = $class;

        $content = str(file_get_contents($to));

        if ($content->contains(['\\' . $basename . ';', '\\' . $basename . ','])) {
            return;
        }

        $tab = static fn (int $times = 1): string => str_repeat(' ', $times * 4);

        $headSection = $content->before('class ');
        $replaceContent = $between($content);

        if ($content->contains($use)) {
            $use = '';
        }

        $content = str_replace(
            [
                $headSection->value(),
                $replaceContent->value(),
            ],
            [
                $headSection->replaceLast(';', (";\nuse $namespace;" . ($use ? "\nuse $use;" : ''))),
                $replace($replaceContent, $tab)->value(),
            ],
            $content->value(),
        );

        file_put_contents($to, $content);
    }

    protected function replaceInConfig(
        string $key,
        string $value,
        ?string $classReplace = null,
    ): void {
        $replace = "'$key' => $value,";

        $pattern = \is_null($classReplace) ?
            "/['\"]" . $key . "['\"]\s*=>\s*[^'\"]+?,/"
            : "/['\"]" . $key . "['\"]\s*=>\s*" . $classReplace . "::class,/";

        file_put_contents(
            config_path('moonshine.php'),
            preg_replace([
                $pattern,
            ], $replace, file_get_contents(config_path('moonshine.php'))),
        );
    }

    protected function makeViewFromStub(string $path, string $name, string $dir = ''): string
    {
        $suggestView = str($name)
            ->classBasename()
            ->kebab()
            ->prepend(
                $path . '.' . str($dir)
                    ->replace('/', '.')
                    ->lower()
                    ->whenNotEmpty(fn(Stringable $str) => $str->append('.')),
            )
            ->value();

        $view = text(
            'Path to view',
            $suggestView,
            default: $suggestView,
            required: true,
        );

        $view = str_replace('.blade.php', '', $view);
        $viewPath = resource_path('views/' . str_replace('.', DIRECTORY_SEPARATOR, $view));
        $viewPath .= '.blade.php';

        $this->makeDir(
            \dirname($viewPath),
        );

        $this->copyStub('view', $viewPath);

        outro(
            "View was created: " . $this->getRelativePath($viewPath),
        );

        return $view;
    }

    protected function fastCreateFromStub(string $stub, string $dir): void
    {
        $className = $this->argument('className') ?? text(
            'Class name',
            required: true
        );

        $stubsPath = new StubsPath($className, 'php');

        $stubsPath->prependDir(
            $this->getDirectory($dir)
        )->prependNamespace(
            moonshineConfig()->getNamespace($dir)
        );

        $this->makeDir($stubsPath->dir);

        $this->copyStub($stub, $stubsPath->getPath(), [
            '{namespace}' => $stubsPath->namespace,
            'DummyClass' => $stubsPath->name,
        ]);

        $this->wasCreatedInfo($stubsPath);
    }

    protected function wasCreatedInfo(StubsPath $stubsPath): void
    {
        outro(
            "$stubsPath->name was created: " . $this->getRelativePath($stubsPath->getPath()),
        );
    }
}
