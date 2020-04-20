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
use RuntimeException;
use InvalidArgumentException;

use function strtoupper;
use function strtolower;

/**
 * @covers \BitFrame\Renderer\TemplateRenderer
 */
class TemplateRendererTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/Asset/';

    private TemplateRenderer $renderer;

    public function setUp(): void
    {
        $this->renderer = new TemplateRenderer();
    }

    public function fileExtensionProvider(): array
    {
        return [
            'empty' => ['', TemplateRenderer::DEFAULT_FILE_EXT],
            'html' => ['html', 'html'],
            'long' => ['somethingreallylong', 'somethingreallylong'],
            'mixed case' => ['mixedCase', 'mixedcase'],
            'upper case' => ['UPPER', 'upper'],
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
        $renderer = new TemplateRenderer($fileExt);

        $this->assertSame($expected, $renderer->getFileExtension());
    }

    public function testAddAndGetFolder(): void
    {
        $this->renderer->addFolder('test', 'directory/to/templates');
        $this->renderer->addFolder('foo', 'bar/baz/qux');

        $this->assertSame([
            'test' => 'directory/to/templates',
            'foo' => 'bar/baz/qux',
        ], $this->renderer->getFolders());

        $this->assertSame('directory/to/templates', $this->renderer->getFolderPathByAlias('test'));
        $this->assertSame('bar/baz/qux', $this->renderer->getFolderPathByAlias('foo'));
    }

    public function testAddingFolderTwiceThrowsException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->renderer->getFolderPathByAlias('non-existent');
    }

    public function testGetFolderPathByAliasForNonExistingFolderShouldThrowException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->renderer->addFolder('test', 'directory/to/templates');
        $this->renderer->addFolder('test', 'foo/bar/baz/qux');
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

    public function testCreateTemplateByName(): void
    {
        $this->renderer->addFolder('foo', 'bar/baz/qux');
        $tpl = $this->renderer->createTemplateByName('foo::bar');

        $this->assertInstanceOf(Template::class, $tpl);
    }

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
    public function testCreateTemplateByNameWithInvalidNameShouldThrowException(
        string $name
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->renderer->createTemplateByName($name);
    }

    public function testRender(): void
    {
        $tpl = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $tpl->method('render')->willReturn('hello world!');

        /** @var \PHPUnit\Framework\MockObject\MockObject|TemplateRenderer $renderer */
        $renderer = $this->getMockBuilder(TemplateRenderer::class)
            ->onlyMethods(['createTemplateByName'])
            ->getMock();

        $renderer->method('createTemplateByName')->willReturn($tpl);

        $this->assertSame('hello world!', $renderer->render('foo'));
    }

    public function testTemplateCanUseGlobalAndLocalFunction(): void
    {
        $renderer = new TemplateRenderer();
        $renderer->addFolder('assets', self::ASSETS_DIR);
        $renderer->withData(['uppercase' => static fn (string $arg): string => strtoupper($arg)]);

        $output = $renderer->render('assets::functions', [
            'lowercase' => static fn (string $arg): string => strtolower($arg),
        ]);

        $this->assertSame('HELLO world!', $output);
    }

    public function testTemplateCanUseGlobalAndLocalObjectMethods(): void
    {
        $renderer = new TemplateRenderer();
        $renderer->addFolder('assets', self::ASSETS_DIR);

        $output = $renderer->render('assets::object', [
            'str' => new StringUtil(),
        ]);

        $this->assertSame('HELLO world!', $output);
    }

    public function testTemplateCanBatchApplyFunctions(): void
    {
        $renderer = new TemplateRenderer();
        $renderer->addFolder('assets', self::ASSETS_DIR);

        $output = $renderer->render('assets::batch', [
            'uppercase' => static fn (string $arg): string => strtoupper($arg),
            'escape' => StringUtil::class . '::escape',
        ]);

        $this->assertSame('&lt;A HREF=&#039;#&#039;&gt;TEST&lt;/A&gt;', $output);
    }
}
