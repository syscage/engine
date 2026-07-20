<?php

declare(strict_types=1);

namespace Syscage\Engine\Tests\Unit;

use Syscage\Engine\Filesystem\Filesystem;
use Syscage\Engine\Generator\ClassGenerator;
use Syscage\Engine\Generator\StubEngine;
use Syscage\Engine\Support\PathResolver;
use Syscage\Engine\Tests\TestCase;

final class ClassGeneratorTest extends TestCase
{
    private string $sandbox;

    private ClassGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandbox = sys_get_temp_dir() . '/syscage-engine-tests-' . uniqid();

        $files = new Filesystem();
        $stubs = new StubEngine($files, $this->app->make(PathResolver::class));

        $this->generator = new ClassGenerator($files, $stubs);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->sandbox)) {
            (new \Illuminate\Filesystem\Filesystem())->deleteDirectory($this->sandbox);
        }

        parent::tearDown();
    }

    public function test_resolve_path_maps_a_fqcn_onto_its_namespace_root(): void
    {
        $path = $this->generator->resolvePath(
            'Plugin\\WhatsApp\\Http\\Controllers\\WebhookController',
            'Plugin\\WhatsApp',
            '/plugins/whatsapp/src',
        );

        $this->assertSame(
            '/plugins/whatsapp/src' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'WebhookController.php',
            $path,
        );
    }

    public function test_generate_writes_a_rendered_class_file_to_disk(): void
    {
        $destination = $this->generator->generate(
            stub: 'class',
            fullyQualifiedClassName: 'Plugin\\Demo\\Services\\DemoService',
            namespaceRoot: 'Plugin\\Demo',
            pathRoot: $this->sandbox . '/src',
            replacements: ['description' => 'Generated for testing.'],
        );

        $this->assertFileExists($destination);

        $contents = file_get_contents($destination);

        $this->assertStringContainsString('namespace Plugin\\Demo\\Services;', $contents);
        $this->assertStringContainsString('class DemoService', $contents);
        $this->assertStringContainsString('Generated for testing.', $contents);
    }

    public function test_generate_does_not_overwrite_an_existing_file_unless_forced(): void
    {
        $destination = $this->generator->generate(
            stub: 'class',
            fullyQualifiedClassName: 'Plugin\\Demo\\Services\\DemoService',
            namespaceRoot: 'Plugin\\Demo',
            pathRoot: $this->sandbox . '/src',
        );

        file_put_contents($destination, '// hand edited');

        $this->generator->generate(
            stub: 'class',
            fullyQualifiedClassName: 'Plugin\\Demo\\Services\\DemoService',
            namespaceRoot: 'Plugin\\Demo',
            pathRoot: $this->sandbox . '/src',
        );

        $this->assertSame('// hand edited', file_get_contents($destination));

        $this->generator->generate(
            stub: 'class',
            fullyQualifiedClassName: 'Plugin\\Demo\\Services\\DemoService',
            namespaceRoot: 'Plugin\\Demo',
            pathRoot: $this->sandbox . '/src',
            force: true,
        );

        $this->assertNotSame('// hand edited', file_get_contents($destination));
    }
}
