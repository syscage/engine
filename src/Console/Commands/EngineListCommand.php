<?php

declare(strict_types=1);

namespace Syscage\Engine\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Engine\Contracts\PackageManifestContract;
use Syscage\Engine\Core\EngineManager;

final class EngineListCommand extends Command
{
    protected $signature = 'engine:list {--type=all : Filter by "packages", "plugins", or "all"}';

    protected $description = 'List every registered SysCage package and/or plugin';

    public function handle(EngineManager $engine): int
    {
        $type = (string) $this->option('type');

        if ($type === 'packages' || $type === 'all') {
            $this->renderTable('Packages', $engine->packages()->all());
        }

        if ($type === 'plugins' || $type === 'all') {
            $this->renderTable('Plugins', $engine->plugins()->all());
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, PackageManifestContract>  $manifests
     */
    private function renderTable(string $label, array $manifests): void
    {
        $this->components->twoColumnDetail("<fg=cyan;options=bold>{$label}</>");

        if ($manifests === []) {
            $this->components->twoColumnDetail('  <fg=gray>none registered</>');

            return;
        }

        $this->table(
            ['Name', 'Version', 'Enabled', 'Provider'],
            array_map(static fn (PackageManifestContract $manifest): array => [
                $manifest->displayName(),
                $manifest->version(),
                $manifest->isEnabled() ? 'yes' : 'no',
                $manifest->provider() ?? '—',
            ], array_values($manifests)),
        );
    }
}
