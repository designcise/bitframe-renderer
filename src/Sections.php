<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Renderer;

final class Sections
{
    /** @var string */
    public const ADD = 'add';

    /** @var string */
    public const APPEND = 'append';

    /** @var string */
    public const PREPEND = 'prepend';

    public function __construct(private array $sections = [])
    {
    }

    public function add(string $name, string $content): void
    {
        $this->sections[$name] = $content;
    }

    public function append(string $name, string $content): void
    {
        $this->sections[$name] = ($this->get($name) ?: '') . $content;
    }

    public function prepend(string $name, string $content): void
    {
        $this->sections[$name] = $content . ($this->get($name) ?: '');
    }

    public function get(string $name): ?string
    {
        return $this->sections[$name] ?? null;
    }
}
