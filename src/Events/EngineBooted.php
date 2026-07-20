<?php

declare(strict_types=1);

namespace Syscage\Engine\Events;

/**
 * Fired once the engine has finished discovering and registering
 * every package/plugin during the application boot cycle.
 */
final class EngineBooted
{
    public function __construct(
        public readonly int $packageCount,
        public readonly int $pluginCount,
    ) {
    }
}
