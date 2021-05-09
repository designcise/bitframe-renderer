<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Renderer\Test;

use PHPUnit\Framework\TestCase;
use BitFrame\Renderer\{Renderer, RenderContext, Sections, Template};
use RuntimeException;

/**
 * @covers \BitFrame\Renderer\RenderContext
 */
class RenderContextTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/Asset/';

    private RenderContext $context;

    /** @var callable */
    private $fetchCallback;

    public function setUp(): void
    {
        $this->fetchCallback = static function () {};
        $this->context = new RenderContext($this->fetchCallback);
    }

    public function testCanSetParent(): void
    {
        $this->context->parent('blah2::layout', ['foo' => 'bar']);

        $this->assertSame('blah2::layout', $this->context->getParentTemplate());
        $this->assertSame(['foo' => 'bar'], $this->context->getParentData());
    }

    /**
     * @throws \Throwable
     */
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

    /**
     * @throws \Throwable
     */
    public function testStartAndEnd(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section', $renderer);
        $tpl->render();

        $this->assertSame('foobar', $tpl->getSections()->get('test'));
    }

    /**
     * @throws \Throwable
     */
    public function testAppend(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section_append', $renderer);
        $tpl->render();

        $this->assertSame('foobar', $tpl->getSections()->get('test'));
    }

    /**
     * @throws \Throwable
     */
    public function testPrepend(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section_prepend', $renderer);
        $tpl->render();

        $this->assertSame('barfoo', $tpl->getSections()->get('test'));
    }

    public function testSection(): void
    {
        $sections = new Sections(['foo' => 'bar']);
        $context = new RenderContext($this->fetchCallback, [], $sections);

        $this->assertSame('bar', $context->section('foo', 'default'));
    }

    public function testCanGetDefaultForNonExistentSection(): void
    {
        $sections = new Sections(['foo' => 'bar']);
        $context = new RenderContext($this->fetchCallback, [], $sections);

        $this->assertSame('default', $context->section('non-existent', 'default'));
    }

    public function testGetData(): void
    {
        $data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $context = new RenderContext($this->fetchCallback, $data);

        $this->assertSame($data, $context->getData());
    }

    /**
     * @throws \Throwable
     */
    public function testFetch(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section', $renderer);
        $context = new RenderContext([$tpl, 'fetch']);

        $output = $context->fetch('assets::helloworld');

        $this->assertSame('hello world', $output);
    }

    public function testGetContent(): void
    {
        $callable = fn () => null;
        $childContent = '<p>test</p>';
        $context = new RenderContext($callable, [], null, $childContent);

        $this->assertSame($childContent, $context->getContent());
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
        $data = [
            'foo' => static function ($string) {
                return 'Test: ' . $string;
            },
        ];
        $context = new RenderContext($this->fetchCallback, $data);

        $this->assertSame('TEST: HELLO!', $context->apply('Hello!', 'foo|strtoupper'));
    }
}
