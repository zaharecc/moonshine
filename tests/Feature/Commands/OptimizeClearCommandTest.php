<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Core\Collections\AutoloadCollection;
use MoonShine\Laravel\Commands\OptimizeClearCommand;
use MoonShine\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(OptimizeClearCommand::class)]
#[Group('commands')]
final class OptimizeClearCommandTest extends TestCase
{
    #[Test]
    #[TestDox('it successfully delete the cache file')]
    public function deletedSuccessfully(): void
    {
        $path = $this->app->make(AutoloadCollection::class)->file();

        file_put_contents($path, "<?php\nreturn [];");

        $this->assertFileExists($path);

        $this->artisan(OptimizeClearCommand::class)
            ->expectsOutputToContain('Clearing cached moonshine file.')
            ->expectsOutputToContain('MoonShine\'s cache has been cleared.')
            ->assertSuccessful();

        $this->assertFileDoesNotExist($path);
    }

    #[Test]
    #[TestDox('it successfully delete the cache file')]
    public function notExist(): void
    {
        $path = $this->app->make(AutoloadCollection::class)->file();

        if (file_exists($path)) {
            @unlink($path);
        }

        $this->assertFileDoesNotExist($path);

        $this->artisan(OptimizeClearCommand::class)
            ->expectsOutputToContain('Clearing cached moonshine file.')
            ->doesntExpectOutputToContain('MoonShine\'s cache has been cleared.')
            ->assertSuccessful();

        $this->assertFileDoesNotExist($path);
    }
}
