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

use function strtoupper;
use function strtolower;
use function array_merge;

/**
 * @covers \BitFrame\Renderer\Renderer
 */
class RendererTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/Asset/';

    private Renderer $renderer;

    public function setUp(): void
    {
        $this->renderer = new Renderer([]);
    }

    public function fileExtensionProvider(): array
    {
        return [
            'empty' => ['', Renderer::DEFAULT_FILE_EXT],
            'html' => ['html', 'html'],
            'long' => ['somethingreallylong', 'somethingreallylong'],
            'mixed case' => ['mixedCase', 'mixedCase'],
            'upper case' => ['UPPER', 'UPPER'],
            'double' => ['tpl.html', 'tpl.html'],
        ];
    }

    /**
     * @dataProvider fileExtensionProvider
     *
     * @param string $fileExt
     * @param string $expected
     */
    public function testSetAndGetFileExtension(string $fileExt, string $expected): void
    {
        $renderer = new Renderer([], $fileExt);

        $this->assertSame($expected, $renderer->getFileExt());
    }

    public function testAddAndGetPath(): void
    {
        $renderer = new Renderer([
            'test' => 'directory/to/templates',
            'foo' => 'bar/baz/qux',
        ]);

        $this->assertSame([
            'test' => 'directory/to/templates',
            'foo' => 'bar/baz/qux',
        ], $renderer->getPaths());

        $this->assertSame('directory/to/templates', $renderer->getPathByName('test'));
        $this->assertSame('bar/baz/qux', $renderer->getPathByName('foo'));
    }

    public function testAddingNonExistentPathThrowsException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->renderer->getPathByName('non-existent');
    }

    public function dataProvider(): array
    {
        return [
            'empty' => [
                [],
                [],
                null
            ],
            'global vars' => [
                ['foo' => 'bar'],
                [],
                null
            ],
            'local vars' => [
                [],
                ['foo' => 'bar'],
                'helloworld'
            ],
            'mix vars' => [
                ['foo' => 'bar'],
                ['baz' => 'qux'],
                'helloworld'
            ],
            'locals override global vars' => [
                ['foo' => 'bar'],
                ['foo' => 'qux'],
                'helloworld'
            ],
            'multiple vars' => [
                ['foo' => 'bar', 'baz' => 'qux'],
                ['hello' => 'world'],
                'helloworld'
            ],
            'no template' => [
                ['foo' => 'bar', 'baz' => 'qux'],
                ['hello' => 'world'],
                null
            ],
            'empty template' => [
                ['foo' => 'bar', 'baz' => 'qux'],
                ['hello' => 'world'],
                ''
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array $sharedVars
     * @param array $tplSpecificVars
     * @param string|array|null $template
     */
    public function testAddAndGetData(
        array $sharedVars,
        array $tplSpecificVars,
        $template
    ): void {
        $this->renderer->withData($sharedVars);
        $this->renderer->withData($tplSpecificVars, $template);

        $this->assertSame(
            array_merge($sharedVars, $tplSpecificVars),
            $this->renderer->getData($template)
        );
    }

    public function testcreateTemplate(): void
    {
        $renderer = new Renderer(['foo' => self::ASSETS_DIR]);
        $tpl = $renderer->createTemplate('foo::helloworld');

        $this->assertInstanceOf(Template::class, $tpl);
    }

    /**
     * @throws \Throwable
     */
    public function testRender(): void
    {
        $tpl = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $tpl->method('render')->willReturn('hello world!');

        /** @var \PHPUnit\Framework\MockObject\MockObject|Renderer $renderer */
        $renderer = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createTemplate'])
            ->getMock();

        $renderer->method('createTemplate')->willReturn($tpl);

        $this->assertSame('hello world!', $renderer->render('foo'));
    }

    /**
     * @throws \Throwable
     */
    public function testTemplateCanUseGlobalAndLocalFunction(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);
        $renderer->withData(['uppercase' => static fn (string $arg): string => strtoupper($arg)]);

        $output = $renderer->render('assets::functions', [
            'lowercase' => static fn (string $arg): string => strtolower($arg),
        ]);

        $this->assertSame('HELLO world!', $output);
    }

    /**
     * @throws \Throwable
     */
    public function testTemplateCanUseGlobalAndLocalObjectMethods(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);

        $output = $renderer->render('assets::object', [
            'str' => new StringUtil(),
        ]);

        $this->assertSame('HELLO world!', $output);
    }

    /**
     * @throws \Throwable
     */
    public function testTemplateCanBatchApplyFunctions(): void
    {
        $renderer = new Renderer(['assets' => self::ASSETS_DIR]);

        $output = $renderer->render('assets::apply', [
            'uppercase' => static fn (string $arg): string => strtoupper($arg),
            'escape' => StringUtil::class . '::escape',
        ]);

        $this->assertSame('&lt;A HREF=&#039;#&#039;&gt;TEST&lt;/A&gt;', $output);
    }
}
