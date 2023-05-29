<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Renderer\Test;

use stdClass;
use PHPUnit\Framework\TestCase;
use BitFrame\Renderer\Data;
use TypeError;

use function array_merge;

/**
 * @covers \BitFrame\Renderer\Data
 */
class DataTest extends TestCase
{
    private Data $data;

    public function setUp(): void
    {
        $this->data = new Data();
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
    public function testAddAndGet(
        array $sharedVars,
        array $tplSpecificVars,
        $template,
    ): void {
        $this->data->add($sharedVars);
        $this->data->add($tplSpecificVars, $template);

        $this->assertSame(
            array_merge($sharedVars, $tplSpecificVars),
            $this->data->get($template)
        );
    }

    public function testAddToMultipleTemplates(): void
    {
        $templates = ['first', 'second', 'third'];

        $this->data->add(['foo' => 'bar', 'baz' => 'qux']);
        $this->data->add(['hello' => 'world'], $templates);

        foreach ($templates as $tpl) {
            $this->assertSame(
                ['foo' => 'bar', 'baz' => 'qux', 'hello' => 'world'],
                $this->data->get($tpl)
            );
        }
    }

    public function testAddMultipleLocalVars(): void
    {
        $templates = ['first', 'second'];

        $this->data->add(['foo' => 'bar']);
        $this->data->add(['hello' => 'world'], $templates);
        $this->data->add(['baz' => 'qux'], 'first');

        $this->assertSame(
            ['foo' => 'bar', 'hello' => 'world', 'baz' => 'qux'],
            $this->data->get('first')
        );
    }

    public function invalidTemplateProvider(): array
    {
        return [
            'boolean false' => [false],
            'boolean true' => [true],
            'function' => [fn () => 'hello world'],
            'object' => [new stdClass()],
        ];
    }

    /**
     * @dataProvider invalidTemplateProvider
     *
     * @param mixed $template
     */
    public function testAddingInvalidTemplateTypeShouldThrowException(mixed $template): void
    {
        $this->expectException(TypeError::class);
        $this->data->add(['foo' => 'bar'], $template);
    }
}
