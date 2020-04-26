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

    public function testApplyThrowsErrorWhenFunctionNotFound(): void
    {
        $tpl = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = new RenderContext($tpl);

        $this->expectException(RuntimeException::class);

        $context->apply('test', 'strtoupper|nonexistent');
    }

    public function testApplyCanApplyCallables(): void
    {
        $tpl = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = new RenderContext($tpl);

        $output = $context->apply('TEST ME', 'strtolower|ucwords');

        $this->assertSame('Test Me', $output);
    }
}
