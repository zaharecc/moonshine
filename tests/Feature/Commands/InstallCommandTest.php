<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Laravel\Commands\InstallCommand;
use MoonShine\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(InstallCommand::class)]
#[Group('commands')]
final class InstallCommandTest extends TestCase
{
    #[Test]
    #[TestDox('it successful installed')]
    public function successfulCreated(): void
    {
        $this->artisan(InstallCommand::class, [
            '--tests-mode' => true,
        ])
            ->expectsOutputToContain('Vendor published')
            ->expectsOutputToContain('Storage link created')
            ->expectsOutputToContain('Resources directory created')
            ->expectsOutputToContain('Dashboard created')
            ->expectsOutputToContain('Layout published')
            ->expectsOutputToContain('Installation completed')
            ->assertSuccessful()
        ;
    }
}
