<?php

declare(strict_types=1);

namespace Syscage\Engine\Providers;

use Illuminate\Support\ServiceProvider;
use Syscage\Engine\Bootstrap\RegistersBindings;
use Syscage\Engine\Bootstrap\RegistersPublishing;
use Syscage\Engine\Console\Commands\EngineDiscoverCommand;
use Syscage\Engine\Console\Commands\EngineListCommand;
use Syscage\Engine\Core\EngineManager;

/**
 * Boots the SysCage Engine: registers every generic service the rest
 * of the ecosystem depends on. Contains no plugin-specific business
 * logic whatsoever — only foundation-level wiring.
 */
final class EngineServiceProvider extends ServiceProvider
{
    use RegistersBindings;
    use RegistersPublishing;

    public function register(): void
    {
        $this->mergeConfigFrom($this->basePath() . '/config/engine.php', 'engine');

        $this->registerBindings($this->app);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing($this->basePath());

            $this->commands([
                EngineDiscoverCommand::class,
                EngineListCommand::class,
            ]);
        }

        $this->loadViewsFrom($this->basePath() . '/resources/views', 'engine');

        if ((bool) config('engine.discovery.eager', true)) {
            $this->app->make(EngineManager::class)->discoverAll();
        }
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            EngineManager::class,
        ];
    }

    private function basePath(): string
    {
        return dirname(__DIR__, 2);
    }
}
