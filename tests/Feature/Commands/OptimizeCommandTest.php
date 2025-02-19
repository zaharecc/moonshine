<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Core\Collections\AutoloadCollection;
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
        $path = $this->app->make(AutoloadCollection::class)->file();

        if (file_exists($path)) {
            @unlink($path);
        }

        $this->assertFileDoesNotExist($path);

        $this->artisan(OptimizeCommand::class)
            ->expectsOutputToContain('Caching MoonShine pages and resources.')
            ->expectsOutputToContain('Search')
            ->expectsOutputToContain('Storing')
            ->assertSuccessful();

        $this->assertFileExists($path);

        $content = require $path;

        $this->assertIsArray($content);
        $this->assertArrayHasKey('pages', $content);
        $this->assertArrayNotHasKey('resources', $content);
    }
}
