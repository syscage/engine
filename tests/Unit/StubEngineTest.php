<?php

declare(strict_types=1);

namespace Syscage\Engine\Tests\Unit;

use Syscage\Engine\Exceptions\StubNotFoundException;
use Syscage\Engine\Filesystem\Filesystem;
use Syscage\Engine\Generator\StubEngine;
use Syscage\Engine\Support\PathResolver;
use Syscage\Engine\Tests\TestCase;

final class StubEngineTest extends TestCase
{
    private StubEngine $stubs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stubs = new StubEngine(
            new Filesystem(),
            $this->app->make(PathResolver::class),
        );
    }

    public function test_it_replaces_simple_placeholders(): void
    {
        $result = $this->stubs->renderString('namespace {{ namespace }}; class {{ class }} {}');

        $this->assertSame(
            'namespace App\Plugin; class Webhook {}',
            $this->stubs->renderString('namespace {{ namespace }}; class {{ class }} {}', [
                'namespace' => 'App\Plugin',
                'class' => 'Webhook',
            ]),
        );

        // Missing placeholders resolve to an empty string rather than erroring.
        $this->assertSame('namespace ; class {}', str_replace('  ', ' ', $result));
    }

    public function test_it_supports_dot_notation_placeholders(): void
    {
        $result = $this->stubs->renderString('{{ meta.author }}', ['meta' => ['author' => 'SysCage']]);

        $this->assertSame('SysCage', $result);
    }

    public function test_if_block_renders_when_flag_is_truthy(): void
    {
        $template = '{{#if description}}Description: {{ description }}{{/if}}';

        $this->assertSame(
            'Description: Handles inbound webhooks',
            $this->stubs->renderString($template, ['description' => 'Handles inbound webhooks']),
        );
    }

    public function test_if_block_falls_through_to_else(): void
    {
        $template = '{{#if description}}{{ description }}{{else}}No description provided{{/if}}';

        $this->assertSame('No description provided', $this->stubs->renderString($template, ['description' => false]));
    }

    public function test_unless_block_is_the_inverse_of_if(): void
    {
        $template = '{{#unless is_abstract}}final {{/unless}}class {{ class }}';

        $this->assertSame('final class Foo', $this->stubs->renderString($template, ['class' => 'Foo', 'is_abstract' => false]));
        $this->assertSame('class Foo', $this->stubs->renderString($template, ['class' => 'Foo', 'is_abstract' => true]));
    }

    public function test_it_renders_the_bundled_default_class_stub(): void
    {
        $path = $this->stubs->resolveStubPath('class');
        $rendered = $this->stubs->render($path, [
            'namespace' => 'Plugin\\Demo',
            'class' => 'DemoController',
            'description' => 'A demo controller.',
        ]);

        $this->assertStringContainsString('namespace Plugin\\Demo;', $rendered);
        $this->assertStringContainsString('class DemoController', $rendered);
        $this->assertStringContainsString('A demo controller.', $rendered);
    }

    public function test_it_throws_when_a_stub_cannot_be_resolved(): void
    {
        $this->expectException(StubNotFoundException::class);

        $this->stubs->resolveStubPath('does-not-exist');
    }
}
