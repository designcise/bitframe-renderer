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

use PHPUnit\Framework\TestCase;
use BitFrame\Renderer\Sections;

/**
 * @covers \BitFrame\Renderer\Sections
 */
class SectionsTest extends TestCase
{
    private Sections $sections;

    public function setUp(): void
    {
        $this->sections = new Sections();
    }

    public function testConstructor(): void
    {
        $sections = new Sections(['test' => 'hello', 'foo' => 'bar']);

        $this->assertSame('hello', $sections->get('test'));
        $this->assertSame('bar', $sections->get('foo'));
    }

    public function testAdd(): void
    {
        $this->sections->add('test', 'foobar');

        $this->assertSame('foobar', $this->sections->get('test'));
    }

    public function testAddingTwiceShouldOverwrite(): void
    {
        $this->sections->add('test', 'foobar');
        $this->sections->add('test', '1234');

        $this->assertSame('1234', $this->sections->get('test'));
    }

    public function testAppend(): void
    {
        $this->sections->add('test', 'foobar');
        $this->sections->append('test', 'baz');

        $this->assertSame('foobarbaz', $this->sections->get('test'));
    }

    public function testPrepend(): void
    {
        $this->sections->add('test', 'foobar');
        $this->sections->prepend('test', 'baz');

        $this->assertSame('bazfoobar', $this->sections->get('test'));
    }
}
