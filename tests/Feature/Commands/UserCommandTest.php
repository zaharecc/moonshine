<?php

declare(strict_types=1);

namespace MoonShine\Tests\Feature\Commands;

use MoonShine\Laravel\Commands\MakeUserCommand;
use MoonShine\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(MakeUserCommand::class)]
#[Group('commands')]
final class UserCommandTest extends TestCase
{
    #[Test]
    #[TestDox('it successful user created')]
    public function successfulCreated(): void
    {
        $this->assertDatabaseMissing('moonshine_users', [
            'email' => 'danil@moonshine.com',
        ]);

        $this->artisan(MakeUserCommand::class, [
            '--name' => 'Danil',
            '--username' => 'danil@moonshine.com',
            '--password' => 'danil@moonshine.com',
        ])
            ->assertSuccessful();

        $this->assertDatabaseHas('moonshine_users', [
            'email' => 'danil@moonshine.com',
        ]);
    }
}
