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
use BitFrame\Renderer\{Renderer, Template};
use BitFrame\Renderer\Test\Asset\StringUtil;
use RuntimeException;
use InvalidArgumentException;
use Throwable;

use function strtoupper;
use function strtolower;
use function ob_get_level;
use function ob_get_contents;

/**
 * @covers \BitFrame\Renderer\Template
 */
class TemplateTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/Asset/';

    public function invalidTemplateNameProvider(): array
    {
        return [
            'separator used twice' => ['foo::bar::baz'],
            'separator used twice without ending string' => ['foo::bar::'],
            'separator without ending string' => ['foo::'],
        ];
    }

    /**
     * @dataProvider invalidTemplateNameProvider
     *
     * @param string $name
     */
    public function testcreateTemplateWithInvalidNameShouldThrowException(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Template($name, new Renderer([]));
    }

    public function testWithData(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|Renderer $renderer */
        $renderer = $this->getMockBuilder(Renderer::class)
            ->setConstructorArgs([['test' => self::ASSETS_DIR]])
            ->setMethods(['getData'])
            ->getMock();

        $renderer->method('getData')->willReturn(['foo' => 'bar']);

        $tpl = new Template('test::fetch', $renderer);
        $tpl->withData(['baz' => 'qux']);

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $tpl->getData());
    }

    /**
     * @throws Throwable
     */
    public function testCanReadLocalAndGlobalDataInTemplate(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $renderer->withData(['global' => 'foo']);

        $tpl = new Template('assets::data', $renderer);
        $tpl->withData(['local' => 'bar']);

        $this->assertSame('foobarbaz', $tpl->render(['inline' => 'baz']));
    }

    /**
     * @throws Throwable
     */
    public function testSection(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::section', $renderer);

        $tpl->render();

        $this->assertSame('foobar', $tpl->getSections()->get('test'));
    }

    public function testCreateTemplateForNonExistentFile(): void
    {
        $this->expectException(RuntimeException::class);

        new Template('foo::bar', new Renderer(['foo' => self::ASSETS_DIR]));
    }

    public function testRenderCleansBufferAndThrowsExceptionIfRenderingFails(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::has_errors', $renderer);

        try {
            $tpl->render(['var' => 1]);
        } catch (Throwable $e) {}

        $this->assertSame(1, ob_get_level());
        $this->assertSame('', ob_get_contents());
    }

    public function testGetFilePath(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR], 'html');
        $tpl = new Template('assets::foobar', $renderer);

        $this->assertSame(self::ASSETS_DIR . 'foobar.html', $tpl->getPath());
    }

    public function testGetVar(): void
    {
        $renderer = new Renderer(['foo' => self::ASSETS_DIR]);
        $renderer->withData(['hello' => 'world']);

        $tpl = new Template('foo::helloworld', $renderer);
        $tpl->withData(['test' => 1234]);

        $this->assertSame(1234, $tpl->getVar('test'));
        $this->assertSame('world', $tpl->getVar('hello'));
        $this->assertSame('default', $tpl->getVar('non-existent', 'default'));
    }

    /**
     * @throws Throwable
     */
    public function testCanLoadParentFromChild(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $renderer->withData(['foo' => 'bar']);

        $tpl = new Template('assets::child', $renderer);

        $output = $tpl->render();

        $expected =<<<EXP
<h1>Parent</h1>


<p>Child</p>

<p>bar</p>
EXP;

        $this->assertSame($expected, $output);
    }

    /**
     * @throws Throwable
     */
    public function testCanLoadNestedTemplates(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);

        $tpl = new Template('assets::nested-3', $renderer);

        $output = $tpl->render();

        $expected =<<<EXP
<h1>Parent:</h1>
<ul>
<li>#1;</li>
<li>#2;</li>
<li>#3;</li>
</ul>
EXP;

        $this->assertSame($expected, $output);
    }

    /**
     * @throws Throwable
     */
    public function testCanLoadAppendedContent(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);

        $tpl = new Template('assets::nested-fetch-3', $renderer);

        $output = $tpl->render();

        $expected =<<<EXP
<script>
alert('p-1.2');
alert('p-1.1');
alert('p-2.2');
alert('p-2.1');
alert('p-3.2');
alert('p-3.1');
alert('#3');
alert('a-3.1');
alert('a-3.2');
alert('#2');
alert('a-2.1');
alert('a-2.2');
alert('#1');
alert('a-1.1');
alert('a-1.2');
</script>
EXP;

        $this->assertSame($expected, $output);
    }

    /**
     * @throws Throwable
     */
    public function testTemplateCanUseGlobalAndLocalFunction(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $renderer->withData(['uppercase' => static fn (string $arg): string => strtoupper($arg)]);

        $tpl = new Template('assets::functions', $renderer);

        $output = $tpl->render([
            'lowercase' => static fn (string $arg): string => strtolower($arg),
        ]);

        $this->assertSame('HELLO world!', $output);
    }

    /**
     * @throws Throwable
     */
    public function testTemplateCanUseGlobalAndLocalObjectMethods(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::object', $renderer);

        $output = $tpl->render([
            'str' => new StringUtil(),
        ]);

        $this->assertSame('HELLO world!', $output);
    }

    /**
     * @throws Throwable
     */
    public function testTemplateCanApplyFunctions(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::apply', $renderer);

        $output = $tpl->render([
            'uppercase' => static fn (string $arg): string => strtoupper($arg),
            'escape' => StringUtil::class . '::escape',
        ]);

        $this->assertSame('&lt;A HREF=&#039;#&#039;&gt;TEST&lt;/A&gt;', $output);
    }

    /**
     * @throws Throwable
     */
    public function testFetch(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $tpl = new Template('assets::apply', $renderer);

        $output = $tpl->fetch('assets::helloworld');

        $this->assertSame('hello world', $output);
    }
}
