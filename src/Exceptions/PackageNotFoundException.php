<?php

declare(strict_types=1);

namespace Syscage\Engine\Exceptions;

class PackageNotFoundException extends EngineException
{
    public static function withName(string $name): self
    {
        return new self("No package or plugin named [{$name}] has been registered.");
    }
}
