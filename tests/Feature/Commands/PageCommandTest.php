<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Laravel\Commands\MakePageCommand;
use MoonShine\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(MakePageCommand::class)]
#[Group('commands')]
final class PageCommandTest extends TestCase
{
    #[Test]
    #[TestDox('it successful file created')]
    public function successfulCreated(): void
    {
        $name = 'DeleteMe';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Pages/$file";

        @unlink($path);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakePageCommand::class, [
            'className' => $name,
        ])
            ->expectsQuestion('Type', 'FormPage')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
    }

    #[Test]
    #[TestDox('it successful file created in sub folder')]
    public function successfulCreatedInSubFolder(): void
    {
        $dir = 'Test';
        $name = 'DeleteMe';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Pages/$dir/$file";

        @unlink($path);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakePageCommand::class, [
            'className' => "$dir/$name",
        ])
            ->expectsQuestion('Type', 'IndexPage')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
    }

    #[Test]
    #[TestDox('it successful crud files created')]
    public function successfulCrudCreated(): void
    {
        $name = 'DeleteMe';
        $path1 = __DIR__ . "/../../../app/MoonShine/Pages/$name/{$name}IndexPage.php";
        $path2 = __DIR__ . "/../../../app/MoonShine/Pages/$name/{$name}FormPage.php";
        $path3 = __DIR__ . "/../../../app/MoonShine/Pages/$name/{$name}DetailPage.php";

        @unlink($path1);
        @unlink($path2);
        @unlink($path3);

        $this->assertFileDoesNotExist($path1);
        $this->assertFileDoesNotExist($path2);
        $this->assertFileDoesNotExist($path3);

        $this->artisan(MakePageCommand::class, [
            'className' => $name,
            '--crud' => true,
        ])
            ->expectsOutputToContain(
                "{$name}IndexPage was created"
            )
            ->expectsOutputToContain(
                "{$name}FormPage was created"
            )
            ->expectsOutputToContain(
                "{$name}DetailPage was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path1);
        $this->assertFileExists($path2);
        $this->assertFileExists($path3);
    }
}
