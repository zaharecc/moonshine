<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Laravel\Commands\MakeApplyCommand;
use MoonShine\Laravel\Commands\MakeComponentCommand;
use MoonShine\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(MakeComponentCommand::class)]
#[Group('commands')]
final class ComponentCommandTest extends TestCase
{
    #[Test]
    #[TestDox('it successful file created')]
    public function successfulCreated(): void
    {
        $name = 'DeleteMe';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Components/$file";
        $viewPath = resource_path('views/admin/components/delete-me.blade.php');

        @unlink($path);
        @unlink($viewPath);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakeComponentCommand::class, [
            'className' => $name,
        ])
            ->expectsQuestion('Path to view', 'admin.components.delete-me')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
        $this->assertFileExists($viewPath);
    }

    #[Test]
    #[TestDox('it successful file created in sub folder')]
    public function successfulCreatedInSubFolder(): void
    {
        $dir = 'Test';
        $name = 'DeleteMe';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Components/Test/$file";
        $viewPath = resource_path('views/admin/components/test/delete-me.blade.php');

        @unlink($path);
        @unlink($viewPath);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakeComponentCommand::class, [
            'className' => "/$dir/$name",
        ])
            ->expectsQuestion('Path to view', 'admin.components.test.delete-me')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
        $this->assertFileExists($viewPath);
    }
}
