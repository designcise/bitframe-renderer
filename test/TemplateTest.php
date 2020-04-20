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
use BitFrame\Renderer\{TemplateRenderer, Template};
use BitFrame\Renderer\Test\Asset\StringUtil;

use function strtoupper;
use function strtolower;

/**
 * @covers \BitFrame\Renderer\Template
 */
class TemplateTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/Asset/';

    public function testWithData(): void
    {
        $renderer = $this->getMockBuilder(TemplateRenderer::class)
            ->setMethods(['getData'])
            ->getMock();

        $renderer->method('getData')->willReturn(['foo' => 'bar']);

        $tpl = new Template($renderer, 'test::1234');
        $tpl->withData(['baz' => 'qux']);

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $tpl->getData());
    }

    public function testGetFilePath(): void
    {
        $renderer = new TemplateRenderer('html');
        $renderer->addFolder('foo', 'bar/baz/qux');
        $tpl = new Template($renderer, 'foo::bar');

        $this->assertSame('bar/baz/qux/bar.html', $tpl->getFilePath());
    }

    public function testGetVar(): void
    {
        $renderer = new TemplateRenderer();
        $renderer->addFolder('foo', 'bar/baz/qux');
        $renderer->withData(['hello' => 'world']);

        $tpl = new Template($renderer, 'foo::bar');
        $tpl->withData(['test' => 1234]);

        $this->assertSame(1234, $tpl->getVar('test'));
        $this->assertSame('world', $tpl->getVar('hello'));
        $this->assertSame('default', $tpl->getVar('non-existent', 'default'));
    }

    public function testCanLoadParentFromChild(): void
    {
        $renderer = new TemplateRenderer();
        $renderer->addFolder('assets', self::ASSETS_DIR);
        $renderer->withData(['foo' => 'bar']);

        $tpl = new Template($renderer, 'assets::child');

        $output = $tpl->render();

        $expected =<<<EXP
<h1>Parent</h1>


<p>Child</p>

<p>bar</p>
EXP;

        $this->assertSame($expected, $output);
    }

    public function testTemplateCanUseGlobalAndLocalFunction(): void
    {
        $renderer = new TemplateRenderer();
        $renderer->addFolder('assets', self::ASSETS_DIR);
        $renderer->withData(['uppercase' => static fn (string $arg): string => strtoupper($arg)]);

        $tpl = new Template($renderer, 'assets::functions');

        $output = $tpl->render([
            'lowercase' => static fn (string $arg): string => strtolower($arg),
        ]);

        $this->assertSame('HELLO world!', $output);
    }

    public function testTemplateCanUseGlobalAndLocalObjectMethods(): void
    {
        $renderer = new TemplateRenderer();
        $renderer->addFolder('assets', self::ASSETS_DIR);

        $tpl = new Template($renderer, 'assets::object');

        $output = $tpl->render([
            'str' => new StringUtil(),
        ]);

        $this->assertSame('HELLO world!', $output);
    }

    public function testTemplateCanBatchApplyFunctions(): void
    {
        $renderer = new TemplateRenderer();
        $renderer->addFolder('assets', self::ASSETS_DIR);

        $tpl = new Template($renderer, 'assets::batch');

        $output = $tpl->render([
            'uppercase' => static fn (string $arg): string => strtoupper($arg),
            'escape' => StringUtil::class . '::escape',
        ]);

        $this->assertSame('&lt;A HREF=&#039;#&#039;&gt;TEST&lt;/A&gt;', $output);
    }
}
