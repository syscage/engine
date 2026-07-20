<?php

declare(strict_types=1);

namespace Syscage\Engine\Exceptions;

use RuntimeException;

/**
 * Base class for all exceptions thrown by the SysCage Engine package.
 * Catching this type catches every engine-specific failure mode.
 */
class EngineException extends RuntimeException
{
}
