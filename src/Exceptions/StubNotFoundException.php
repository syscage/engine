<?php

declare(strict_types=1);

namespace Syscage\Engine\Exceptions;

class StubNotFoundException extends EngineException
{
    public static function named(string $name): self
    {
        return new self("No stub named [{$name}] could be resolved from any published or default stub path.");
    }

    public static function atPath(string $path): self
    {
        return new self("The stub file at [{$path}] does not exist.");
    }
}
