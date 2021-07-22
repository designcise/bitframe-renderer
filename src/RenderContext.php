<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Renderer;

use RuntimeException;

use function ob_start;
use function ob_get_clean;
use function explode;
use function is_callable;

class RenderContext
{
    /** @var callable */
    private $fetchTpl;

    public ?string $currSectionName = null;

    public string $newSectionMode = Sections::ADD;

    private string $parentTemplate = '';

    private array $parentData = [];

    public function __construct(
        callable $fetchTpl,
        private array $data = [],
        private ?Sections $sections = null,
        private string $childContent = '',
    ) {
        $this->fetchTpl = $fetchTpl;
    }

    public function parent(string $name, array $data = []): void
    {
        $this->parentTemplate = $name;
        $this->parentData = $data;
    }

    public function start(string $name): void
    {
        if ($this->currSectionName) {
            throw new RuntimeException('Sections cannot be nested within one another.');
        }

        $this->currSectionName = $name;

        ob_start();
    }

    public function append(string $name): void
    {
        $this->newSectionMode = Sections::APPEND;
        $this->start($name);
    }

    public function prepend(string $name): void
    {
        $this->newSectionMode = Sections::PREPEND;
        $this->start($name);
    }

    public function end(): void
    {
        if (null === $this->currSectionName) {
            throw new RuntimeException('You must start a section before you can stop it.');
        }

        $this->sections
            ->{$this->newSectionMode}($this->currSectionName, ob_get_clean());

        $this->currSectionName = null;
        $this->newSectionMode = Sections::ADD;
    }

    public function section(string $name, ?string $default = null): ?string
    {
        return $this->sections->get($name) ?? $default;
    }

    public function getContent(): string
    {
        return $this->childContent;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @param array $data
     *
     * @return string
     *
     * @throws \Throwable
     */
    public function fetch(string $name, array $data = []): string
    {
        return ($this->fetchTpl)($name, $data);
    }

    /**
     * Apply multiple functions to variable.
     *
     * @param mixed $subject
     * @param string $functions
     *
     * @return mixed
     */
    public function apply(mixed $subject, string $functions): mixed
    {
        $functionsList = explode('|', $functions);

        foreach ($functionsList as $function) {
            if ($this->fnExists($function)) {
                $function = $this->data[$function] ?? null;
            }

            if (is_callable($function)) {
                $subject = $function($subject);
                continue;
            }

            throw new RuntimeException(sprintf(
                'The batch function could not find the "%s" function.',
                $function
            ));
        }

        return $subject;
    }

    public function fnExists(string $name): bool
    {
        $func = $this->data[$name] ?? null;
        return (! empty($func) && is_callable($func));
    }

    public function getParentTemplate(): string
    {
        return $this->parentTemplate;
    }

    public function getParentData(): array
    {
        return $this->parentData;
    }
}
