<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Renderer;

final class Sections
{
    private array $sections;

    public function __construct(array $sections = [])
    {
        $this->sections = $sections;
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
