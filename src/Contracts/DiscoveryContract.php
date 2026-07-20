<?php

declare(strict_types=1);

namespace Syscage\Engine\Contracts;

/**
 * A discovery class scans a source (filesystem, composer's installed
 * packages, a directory of drivers, ...) and returns manifests describing
 * what it found. Discoveries never mutate a registry themselves; the
 * caller decides what to do with the results (Single Responsibility).
 */
interface DiscoveryContract
{
    /**
     * Perform the scan and return the manifests that were found.
     *
     * @return array<string, PackageManifestContract>
     */
    public function discover(): array;

    /**
     * A unique identifier for this discovery strategy, used for
     * caching and event dispatching (e.g. "package", "plugin", "driver").
     */
    public function identifier(): string;
}
