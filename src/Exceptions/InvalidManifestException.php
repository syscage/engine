<?php

declare(strict_types=1);

namespace Syscage\Engine\Exceptions;

class InvalidManifestException extends EngineException
{
    public static function missingKey(string $path, string $key): self
    {
        return new self("The manifest at [{$path}] is missing the required key [{$key}].");
    }

    public static function invalidJson(string $path, string $jsonError): self
    {
        return new self("The manifest at [{$path}] contains invalid JSON: {$jsonError}");
    }

    public static function notFound(string $path): self
    {
        return new self("No manifest file could be found at [{$path}].");
    }
}
