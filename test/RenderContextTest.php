<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Renderer\Test;

use PHPUnit\Framework\TestCase;
use BitFrame\Renderer\{Renderer, RenderContext, Template};
use RuntimeException;

/**
 * @covers \BitFrame\Renderer\RenderContext
 */
class RenderContextTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/Asset/';

    private RenderContext $context;

    public function setUp(): void
    {
        $tpl = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = new RenderContext($tpl);
    }

    public function testCanSetParent(): void
    {
        $this->context->parent('blah2::layout', ['foo' => 'bar']);

        $this->assertSame('blah2::layout', $this->context->getParentTemplate());
        $this->assertSame(['foo' => 'bar'], $this->context->getParentData());
    }

    public function testNestedSectionsShouldThrowException(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::nested_sections_error', $renderer);

        $this->expectException(RuntimeException::class);

        $tpl->render();
    }

    public function testEndWithoutStartShouldThrowException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->context->end();
    }

    public function testStartAndEnd(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section', $renderer);
        $tpl->render();

        $this->assertSame('foobar', $tpl->getSections()->get('test'));
    }

    public function testAppend(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section_append', $renderer);
        $tpl->render();

        $this->assertSame('foobar', $tpl->getSections()->get('test'));
    }

    public function testPrepend(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section_prepend', $renderer);
        $tpl->render();

        $this->assertSame('barfoo', $tpl->getSections()->get('test'));
    }

    public function testSection(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section', $renderer);
        $context = new RenderContext($tpl);

        $tpl->render();

        $this->assertSame('foobar', $context->section('test'));
    }

    public function testCanGetDefaultForNonExistentSection(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section', $renderer);
        $context = new RenderContext($tpl);

        $tpl->render();

        $this->assertSame('default', $context->section('non-existent', 'default'));
    }

    public function testGetData(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $renderer->withData(['global' => 'foo']);

        $tpl = new Template('assets::helloworld', $renderer);
        $tpl->withData(['local' => 'bar']);

        $context = new RenderContext($tpl);

        $this->assertSame(['global' => 'foo', 'local' => 'bar'], $context->getData());
    }

    public function testFetch(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section', $renderer);
        $context = new RenderContext($tpl);

        $output = $context->fetch('assets::helloworld');

        $this->assertSame('hello world', $output);
    }

    public function testApplyThrowsErrorWhenFunctionNotFound(): void
    {
        $this->expectException(RuntimeException::class);

        $this->context->apply('test', 'strtoupper|nonexistent');
    }

    public function testApplyCanApplyCallables(): void
    {
        $output = $this->context->apply('TEST ME', 'strtolower|ucwords');

        $this->assertSame('Test Me', $output);
    }

    public function testApplyFunctionFromTemplateLocals(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section', $renderer);
        $context = new RenderContext($tpl);

        $tpl->render([
            'foo' => static function ($string) {
                return 'Test: ' . $string;
            },
        ]);

        $this->assertSame('TEST: HELLO!', $context->apply('Hello!', 'foo|strtoupper'));
    }
}
