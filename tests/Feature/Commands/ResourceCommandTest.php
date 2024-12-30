<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Laravel\Commands\MakeResourceCommand;
use MoonShine\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(MakeResourceCommand::class)]
#[Group('commands')]
final class ResourceCommandTest extends TestCase
{
    #[Test]
    #[TestDox('it successful file created')]
    public function successfulCreated(): void
    {
        $name = 'DeleteMeResource';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Resources/$file";

        @unlink($path);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakeResourceCommand::class, [
            'className' => $name,
        ])
            ->expectsQuestion('Resource type', 'ModelResourceDefault')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
    }

    #[Test]
    #[TestDox('it successful file with pages created')]
    public function successfulWithPagesCreated(): void
    {
        $name = 'DeleteMeResource';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Resources/$file";
        $path1 = __DIR__ . "/../../../app/MoonShine/Pages/DeleteMe/DeleteMeIndexPage.php";
        $path2 = __DIR__ . "/../../../app/MoonShine/Pages/DeleteMe/DeleteMeFormPage.php";
        $path3 = __DIR__ . "/../../../app/MoonShine/Pages/DeleteMe/DeleteMeDetailPage.php";

        @unlink($path);
        @unlink($path1);
        @unlink($path2);
        @unlink($path3);

        $this->assertFileDoesNotExist($path);
        $this->assertFileDoesNotExist($path1);
        $this->assertFileDoesNotExist($path2);
        $this->assertFileDoesNotExist($path3);

        $this->artisan(MakeResourceCommand::class, [
            'className' => $name,
        ])
            ->expectsQuestion('Resource type', 'ModelResourceWithPages')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->expectsOutputToContain(
                "DeleteMeIndexPage was created"
            )
            ->expectsOutputToContain(
                "DeleteMeFormPage was created"
            )
            ->expectsOutputToContain(
                "DeleteMeDetailPage was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
        $this->assertFileExists($path1);
        $this->assertFileExists($path2);
        $this->assertFileExists($path3);
    }

    #[Test]
    #[TestDox('it successful file created in sub folder')]
    public function successfulCreatedInSubFolder(): void
    {
        $dir = 'Test';
        $name = 'DeleteMeResource';
        $file = "$name.php";
        $path = __DIR__ . "/../../../app/MoonShine/Resources/$dir/$file";

        @unlink($path);

        $this->assertFileDoesNotExist($path);

        $this->artisan(MakeResourceCommand::class, [
            'className' => "$dir/$name",
        ])
            ->expectsQuestion('Resource type', 'ModelResourceDefault')
            ->expectsOutputToContain(
                "$name was created"
            )
            ->assertSuccessful();

        $this->assertFileExists($path);
    }
}
