<?php

declare(strict_types=1);

namespace Syscage\Engine\Discovery;

use Illuminate\Contracts\Events\Dispatcher;
use Syscage\Engine\Contracts\FilesystemContract;
use Syscage\Engine\Contracts\PackageManifestContract;
use Syscage\Engine\Core\PackageManifest;
use Syscage\Engine\Support\PathResolver;

/**
 * Scans Composer's own `vendor/composer/installed.json` for every
 * installed package in the `syscage/` vendor namespace and turns
 * each one into a manifest, without requiring those packages to
 * ship their own `plugin.json` file.
 */
final class PackageDiscovery extends AbstractDiscovery
{
    public const string VENDOR_PREFIX = 'syscage/';

    /**
     * @param  array<int, string>  $excluding  Package names to skip (e.g. the engine itself).
     */
    public function __construct(
        FilesystemContract $files,
        private readonly PathResolver $paths,
        private readonly array $excluding = ['syscage/engine'],
        ?Dispatcher $events = null,
    ) {
        parent::__construct($files, $events);
    }

    public function identifier(): string
    {
        return 'package';
    }

    protected function scan(): iterable
    {
        $installedPath = $this->paths->base('vendor/composer/installed.json');

        if (! $this->files->isFile($installedPath)) {
            return [];
        }

        $decoded = json_decode($this->files->get($installedPath), true);

        if (! is_array($decoded)) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $packages */
        $packages = $decoded['packages'] ?? $decoded;

        return array_values(array_filter(
            $packages,
            fn (mixed $package): bool => is_array($package)
                && isset($package['name'])
                && str_starts_with((string) $package['name'], self::VENDOR_PREFIX)
                && ! in_array($package['name'], $this->excluding, true),
        ));
    }

    protected function buildManifest(mixed $source): ?PackageManifestContract
    {
        /** @var array<string, mixed> $package */
        $package = $source;

        $name = (string) $package['name'];
        $basePath = $this->paths->base('vendor/' . $name);

        $providers = $package['extra']['laravel']['providers'] ?? [];

        return PackageManifest::fromArray([
            'name' => str_replace(self::VENDOR_PREFIX, '', $name),
            'display_name' => $package['extra']['syscage']['display_name'] ?? $name,
            'description' => $package['description'] ?? null,
            'version' => ltrim((string) ($package['version'] ?? '0.0.0'), 'v'),
            'enabled' => true,
            'provider' => is_array($providers) ? ($providers[0] ?? null) : null,
        ], $basePath);
    }
}
