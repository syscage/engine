<?php

declare(strict_types=1);

namespace Syscage\Engine\Discovery;

use Syscage\Engine\Contracts\PackageManifestContract;
use Syscage\Engine\Core\PackageManifest;
use Syscage\Engine\Exceptions\InvalidManifestException;
use Syscage\Engine\Support\PathResolver;

/**
 * Scans the configured plugins directory for `<plugin>/plugin.json`
 * manifest files and turns each valid one into a {@see PackageManifest}.
 */
final class PluginDiscovery extends AbstractDiscovery
{
    public const string MANIFEST_FILENAME = 'plugin.json';

    public function __construct(
        \Syscage\Engine\Contracts\FilesystemContract $files,
        private readonly PathResolver $paths,
        ?\Illuminate\Contracts\Events\Dispatcher $events = null,
    ) {
        parent::__construct($files, $events);
    }

    public function identifier(): string
    {
        return 'plugin';
    }

    protected function scan(): iterable
    {
        $pluginsRoot = $this->paths->plugins();

        if (! $this->files->isDirectory($pluginsRoot)) {
            return [];
        }

        return $this->files->glob($pluginsRoot . '/*/' . self::MANIFEST_FILENAME);
    }

    protected function buildManifest(mixed $source): ?PackageManifestContract
    {
        /** @var string $manifestPath */
        $manifestPath = $source;

        if (! $this->files->isFile($manifestPath)) {
            return null;
        }

        try {
            return PackageManifest::fromJsonFile($manifestPath, dirname($manifestPath));
        } catch (InvalidManifestException) {
            // Skip malformed plugin manifests rather than failing the whole boot cycle.
            return null;
        }
    }
}
