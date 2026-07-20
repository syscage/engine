<?php

declare(strict_types=1);

namespace Syscage\Engine\Managers;

use Closure;
use Illuminate\Contracts\Container\Container;
use Syscage\Engine\Contracts\ManagerContract;
use Syscage\Engine\Exceptions\DriverNotFoundException;

/**
 * Reusable base for the Manager pattern (as used by Laravel's own
 * Cache/Queue/Session managers). Dependent SysCage packages extend
 * this to expose a driver-based API — for example
 * `syscage/payment`'s `PaymentManager` resolving "stripe" vs "paypal",
 * or `syscage/chatbot`'s `ChatbotManager` resolving providers.
 *
 * Subclasses only need to implement {@see getDefaultDriver()} and a
 * `create{Driver}Driver()` method per built-in driver; everything
 * else (caching resolved instances, custom `extend()` support) is
 * handled here.
 */
abstract class AbstractManager implements ManagerContract
{
    /**
     * @var array<string, mixed>
     */
    protected array $resolved = [];

    /**
     * @var array<string, Closure>
     */
    protected array $customCreators = [];

    public function __construct(
        protected readonly Container $container,
    ) {
    }

    public function driver(?string $name = null): mixed
    {
        $name ??= $this->getDefaultDriver();

        if ($name === '') {
            throw DriverNotFoundException::named(static::class, '(empty)');
        }

        return $this->resolved[$name] ??= $this->resolve($name);
    }

    public function extend(string $driver, Closure $callback): static
    {
        $this->customCreators[$driver] = $callback;
        unset($this->resolved[$driver]);

        return $this;
    }

    /**
     * Forget a previously resolved driver instance, forcing it to be
     * re-created on next access.
     */
    public function forgetDriver(?string $name = null): static
    {
        unset($this->resolved[$name ?? $this->getDefaultDriver()]);

        return $this;
    }

    protected function resolve(string $name): mixed
    {
        if (isset($this->customCreators[$name])) {
            return $this->customCreators[$name]($this->container, $name);
        }

        $method = 'create' . ucfirst($name) . 'Driver';

        if (! method_exists($this, $method)) {
            throw DriverNotFoundException::named(static::class, $name);
        }

        return $this->{$method}();
    }

    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver()->{$method}(...$parameters);
    }
}
