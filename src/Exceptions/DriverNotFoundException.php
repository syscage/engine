<?php

declare(strict_types=1);

namespace Syscage\Engine\Exceptions;

class DriverNotFoundException extends EngineException
{
    public static function named(string $manager, string $driver): self
    {
        return new self("Driver [{$driver}] is not supported by manager [{$manager}].");
    }
}
