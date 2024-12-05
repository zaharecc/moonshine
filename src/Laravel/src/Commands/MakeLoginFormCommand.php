<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use function Laravel\Prompts\outro;

#[AsCommand(name: 'moonshine:login-form')]
class MakeLoginFormCommand extends MoonShineCommand
{
    protected $signature = 'moonshine:login-form {className?} {--dir=}';

    protected $description = 'Create Login form';

    public function handle(): int
    {
        $className = $this->argument('className') ?? 'LoginForm';

        $dir = $this->option('dir') ?: \dirname($className);
        $className = class_basename($className);

        if ($dir === '.') {
            $dir = 'Forms';
        }

        $loginForm = $this->getDirectory() . "/$dir/$className.php";

        if (! is_dir($this->getDirectory() . "/$dir")) {
            $this->makeDir($this->getDirectory() . "/$dir");
        }

        $this->copyStub('LoginForm', $loginForm, [
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

        $current = config('moonshine.forms.login', 'LoginForm::class');
        $currentShort = class_basename($current);
        $replace = "'login' => " . moonshineConfig()->getNamespace('\Forms\\' . $className) . "::class";

        file_put_contents(
            config_path('moonshine.php'),
            str_replace([
                "'login' => $current::class",
                "'login' => $currentShort::class",
            ], $replace, file_get_contents(config_path('moonshine.php')))
        );

        return self::SUCCESS;
    }
}
