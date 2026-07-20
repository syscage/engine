<?php

declare(strict_types=1);

namespace Syscage\Engine\Tests\Feature;

use Syscage\Engine\Tests\TestCase;

final class ConsoleCommandsTest extends TestCase
{
    public function test_engine_discover_command_reports_discovered_counts(): void
    {
        $this->artisan('engine:discover')
            ->expectsOutputToContain('Discovered')
            ->assertSuccessful();
    }

    public function test_engine_list_command_renders_registered_plugins(): void
    {
        $this->artisan('engine:discover')->run();

        $this->artisan('engine:list', ['--type' => 'plugins'])
            ->assertSuccessful();
    }
}
