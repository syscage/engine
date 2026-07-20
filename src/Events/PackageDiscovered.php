<?php

declare(strict_types=1);

namespace Syscage\Engine\Events;

use Syscage\Engine\Contracts\PackageManifestContract;

/**
 * Fired once for every manifest a discovery pass finds, regardless
 * of which discovery strategy produced it.
 */
final class PackageDiscovered
{
    public function __construct(
        public readonly PackageManifestContract $manifest,
        public readonly string $discoveryIdentifier,
    ) {
    }
}
