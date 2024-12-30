<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Laravel\Commands\MakeComponentCommand;
use MoonShine\Laravel\Commands\MakeFieldCommand;
use MoonShine\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(MakeFieldCommand::class)]
#[Group('commands')]
final class FieldCommandTest extends TestCase
{
    #[Test]
    #[TestDox('it successful file created')]
    public function successfulCreated(): void
    {
        $name = 'DeleteMe';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Fields/$file";
        $viewPath = resource_path('views/admin/fields/delete-me.blade.php');

        @unlink($path);
        @unlink($viewPath);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakeFieldCommand::class, [
            'className' => $name,
        ])
            ->expectsQuestion('Path to view', 'admin.fields.delete-me')
            ->expectsQuestion('Extends', 'Base')
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
        $path = __DIR__ . "/../../../app/MoonShine/Fields/Test/$file";
        $viewPath = resource_path('views/admin/fields/test/delete-me.blade.php');

        @unlink($path);
        @unlink($viewPath);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakeFieldCommand::class, [
            'className' => "/$dir/$name",
        ])
            ->expectsQuestion('Path to view', 'admin.fields.test.delete-me')
            ->expectsQuestion('Extends', 'Base')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
        $this->assertFileExists($viewPath);
    }
}
