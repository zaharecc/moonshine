<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Contracts\Core\DependencyInjection\OptimizerCollectionContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\ResourceContract;
use MoonShine\Laravel\Commands\OptimizeCommand;
use MoonShine\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(OptimizeCommand::class)]
#[Group('commands')]
final class OptimizeCommandTest extends TestCase
{
    #[Test]
    #[TestDox('it successfully make the cache file')]
    public function successfulMakeCache(): void
    {
        $path = $this->app->make(OptimizerCollectionContract::class)->getCachePath();

        if (file_exists($path)) {
            unlink($path);
        }

        $this->assertFileDoesNotExist($path);

        $this->artisan(OptimizeCommand::class)
            ->expectsOutputToContain('Caching MoonShine pages and resources.')
            ->expectsOutputToContain('MoonShine cached successfully.')
            ->assertSuccessful();

        $this->assertFileExists($path);

        $content = require $path;

        $this->assertIsArray($content);
        $this->assertArrayHasKey(PageContract::class, $content);
        $this->assertArrayNotHasKey(ResourceContract::class, $content);
    }
}
