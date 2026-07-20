<?php

declare(strict_types=1);

namespace Syscage\Engine\Discovery;

use Illuminate\Contracts\Events\Dispatcher;
use Syscage\Engine\Contracts\DiscoveryContract;
use Syscage\Engine\Contracts\FilesystemContract;
use Syscage\Engine\Contracts\PackageManifestContract;
use Syscage\Engine\Events\PackageDiscovered;

/**
 * Template Method base for discovery strategies: subclasses only
 * decide *what* to scan and *how* to turn a scan result into a
 * manifest, while the discovery/event lifecycle stays identical
 * across every strategy.
 *
 * @template TSource
 */
abstract class AbstractDiscovery implements DiscoveryContract
{
    public function __construct(
        protected readonly FilesystemContract $files,
        protected readonly ?Dispatcher $events = null,
    ) {
    }

    /**
     * @return array<string, PackageManifestContract>
     */
    final public function discover(): array
    {
        $manifests = [];

        foreach ($this->scan() as $source) {
            $manifest = $this->buildManifest($source);

            if ($manifest === null) {
                continue;
            }

            $manifests[$manifest->name()] = $manifest;

            $this->events?->dispatch(new PackageDiscovered($manifest, $this->identifier()));
        }

        return $manifests;
    }

    /**
     * Produce the raw list of "sources" to turn into manifests
     * (e.g. directory paths, composer package arrays).
     *
     * @return iterable<mixed>
     */
    abstract protected function scan(): iterable;

    /**
     * Turn a single scanned source into a manifest, or null to skip it.
     */
    abstract protected function buildManifest(mixed $source): ?PackageManifestContract;
}
