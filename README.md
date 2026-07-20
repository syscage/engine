# SysCage Engine

[![tests](https://github.com/syscage/engine/actions/workflows/tests.yml/badge.svg)](https://github.com/syscage/engine/actions)
[![Latest Version](https://img.shields.io/packagist/v/syscage/engine.svg)](https://packagist.org/packages/syscage/engine)
[![License](https://img.shields.io/packagist/l/syscage/engine.svg)](LICENSE)

`syscage/engine` is the foundation package of the SysCage ecosystem for
Laravel 12+. It is **not** a plugin — it's the core every other SysCage
package (`syscage/plugin`, `syscage/license`, `syscage/installer`,
`syscage/payment`, `syscage/chatbot`, `syscage/workflow`, `syscage/module`, ...)
depends on for registries, discovery, stub-based code generation, and path
resolution. The engine itself contains no plugin-specific business logic.

## Installation

```bash
composer require syscage/engine
```

The service provider and `Engine` facade are registered automatically via
Laravel package auto-discovery.

Publish the configuration file and default stubs if you want to customise them:

```bash
php artisan vendor:publish --tag=engine-config
php artisan vendor:publish --tag=engine-stubs
```

## What it provides

| Concern | Class |
|---|---|
| Package/plugin manifests | `Syscage\Engine\Core\PackageManifest` |
| Registries | `Syscage\Engine\Registry\PackageRegistry`, `PluginRegistry` |
| Discovery | `Syscage\Engine\Discovery\PluginDiscovery`, `PackageDiscovery`, `DriverDiscovery` |
| Stub rendering | `Syscage\Engine\Generator\StubEngine` |
| Code generation | `Syscage\Engine\Generator\ClassGenerator` |
| Driver/Manager pattern base | `Syscage\Engine\Managers\AbstractManager` |
| Path resolution | `Syscage\Engine\Support\PathResolver` |
| Facade | `Syscage\Engine\Facades\Engine` |

## Usage

### Discovering packages and plugins

```php
use Syscage\Engine\Facades\Engine;

Engine::discoverAll();

foreach (Engine::plugins()->enabled() as $plugin) {
    // $plugin is a PackageManifestContract
}
```

Or from the CLI:

```bash
php artisan engine:discover
php artisan engine:list
php artisan engine:list --type=plugins
```

### Plugin manifests (`plugin.json`)

Plugins are discovered from the directory configured by `engine.plugins_path`
(default: `plugins/`). Each plugin directory must contain a `plugin.json`:

```json
{
    "name": "whatsapp",
    "display_name": "WhatsApp",
    "description": "WhatsApp integration",
    "version": "1.0.0",
    "enabled": true,
    "provider": "Plugin\\WhatsApp\\Providers\\PluginServiceProvider"
}
```

### Generating classes from stubs

```php
use Syscage\Engine\Facades\Engine;

$path = Engine::generator()->generate(
    stub: 'class',
    fullyQualifiedClassName: 'Plugin\\WhatsApp\\Http\\Controllers\\WebhookController',
    namespaceRoot: 'Plugin\\WhatsApp',
    pathRoot: base_path('plugins/whatsapp/src'),
    replacements: ['description' => 'Handles inbound WhatsApp webhooks.'],
);
```

Stub templates support placeholders and conditional sections:

```
namespace {{ namespace }};

{{#if description}}
/**
 * {{ description }}
 */
{{/if}}
class {{ class }}
{
    //
}
```

User-published stubs under `stubs/syscage/` always take priority over a
package's bundled defaults, so every generator across the ecosystem can be
customised without touching vendor code.

### Discovering drivers

Dependent packages (e.g. `syscage/payment` discovering gateway drivers, or
`syscage/chatbot` discovering provider drivers) can reuse the same discovery
machinery:

```php
Engine::discoverDrivers(
    directory: app_path('Payment/Drivers'),
    namespace: 'App\\Payment\\Drivers',
    interface: \App\Payment\Contracts\GatewayDriver::class,
);
```

### The Manager pattern base

```php
use Syscage\Engine\Managers\AbstractManager;

final class PaymentManager extends AbstractManager
{
    public function getDefaultDriver(): string
    {
        return $this->container->make('config')->get('payment.default');
    }

    protected function createStripeDriver(): StripeGateway
    {
        return $this->container->make(StripeGateway::class);
    }
}
```

## Configuration

See [`config/engine.php`](config/engine.php) for the full set of options:
plugin path, eager vs. cached discovery, stub publish path, and cache path.

## Testing

```bash
composer install
composer test
```

## Security

If you discover a security vulnerability, please email security@syscage.com
rather than opening a public issue.

## License

The MIT License (MIT). See [LICENSE](LICENSE) for more information.
