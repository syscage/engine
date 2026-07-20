<?php

declare(strict_types=1);

namespace Syscage\Engine\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Engine\Core\EngineManager;

final class EngineDiscoverCommand extends Command
{
    protected $signature = 'engine:discover';

    protected $description = 'Discover installed SysCage packages and plugins and populate the registries';

    public function handle(EngineManager $engine): int
    {
        $engine->discoverAll();

        $this->components->info(sprintf(
            'Discovered %d package(s) and %d plugin(s).',
            $engine->packages()->count(),
            $engine->plugins()->count(),
        ));

        return self::SUCCESS;
    }
}
