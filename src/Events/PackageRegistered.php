<?php

declare(strict_types=1);

namespace Syscage\Engine\Events;

use Syscage\Engine\Contracts\PackageManifestContract;

/**
 * Fired whenever a manifest is registered into a registry instance.
 */
final class PackageRegistered
{
    public function __construct(
        public readonly PackageManifestContract $manifest,
        public readonly string $registry,
    ) {
    }
}
