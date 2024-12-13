<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Closure;
use Illuminate\Support\Stringable;
use Leeto\PackageCommand\Command;
use MoonShine\MenuManager\MenuItem;

abstract class MoonShineCommand extends Command
{
    protected string $stubsDir = __DIR__ . '/../../stubs';

    protected function getDirectory(): string
    {
        return moonshineConfig()->getDir();
    }

    public static function addResourceOrPageToProviderFile(string $class, bool $page = false, string $prefix = ''): void
    {
        $method = $page ? 'pages' : 'resources';

        self::addResourceOrPageTo(
            class: $prefix . $class,
            to: app_path('Providers/MoonShineServiceProvider.php'),
            isPage: $page,
            between: static fn (Stringable $content): Stringable => $content->betweenFirst("->$method([", ']'),
            replace: static fn (Stringable $content, Closure $tab): Stringable => $content->append("{$tab()}$class::class,\n{$tab(3)}"),
        );
    }

    public static function addResourceOrPageToMenu(string $class, string $title, bool $page = false, string $prefix = ''): void
    {
        self::addResourceOrPageTo(
            class: $prefix . $class,
            to: app_path('MoonShine/Layouts/MoonShineLayout.php'),
            isPage: $page,
            between: static fn (Stringable $content): Stringable => $content->betweenFirst("protected function menu(): array", '}'),
            replace: static fn (Stringable $content, Closure $tab): Stringable => $content->replace("];", "{$tab()}MenuItem::make('{$title}', $class::class),\n{$tab(2)}];"),
            use: MenuItem::class,
        );
    }

    /**
     * @param  Closure(Stringable $content): Stringable  $between
     * @param  Closure(Stringable $content, Closure $tab): Stringable  $replace
     */
    private static function addResourceOrPageTo(string $class, string $to, bool $isPage, Closure $between, Closure $replace, string $use = ''): void
    {
        if (! file_exists($to)) {
            return;
        }

        $dir = $isPage ? 'Pages' : 'Resources';
        $namespace = moonshineConfig()->getNamespace("\\$dir\\") . $class;

        $content = str(file_get_contents($to));

        if ($content->contains('\\' . $class)) {
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

    protected function replaceInConfig(string $key, string $value): void
    {
        $replace = "'$key' => $value,";

        $pattern = "/['\"]" . $key . "['\"]\s*=>\s*[^'\"]+?,/";

        file_put_contents(
            config_path('moonshine.php'),
            preg_replace([
                $pattern,
            ], $replace, file_get_contents(config_path('moonshine.php')))
        );
    }
}
