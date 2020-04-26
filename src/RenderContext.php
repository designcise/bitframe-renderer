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

class RenderContext
{
    /** @var string */
    public const SECTION_ADD = 'add';

    /** @var string */
    public const SECTION_APPEND = 'append';

    /** @var string */
    public const SECTION_PREPEND = 'prepend';

    public ?string $currSectionName = null;

    public string $newSectionMode = self::SECTION_ADD;

    private string $parentTemplate = '';

    private array $parentData = [];

    public function __construct(Template $tpl)
    {
        $this->tpl = $tpl;
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
        $this->newSectionMode = self::SECTION_APPEND;
        $this->start($name);
    }

    public function prepend(string $name): void
    {
        $this->newSectionMode = self::SECTION_PREPEND;
        $this->start($name);
    }

    public function end(): void
    {
        if (null === $this->currSectionName) {
            throw new RuntimeException('You must start a section before you can stop it.');
        }

        $this->tpl
            ->getSections()
            ->{$this->newSectionMode}($this->currSectionName, ob_get_clean());

        $this->currSectionName = null;
        $this->newSectionMode = self::SECTION_ADD;
    }

    /**
     * @param string $name
     * @param string $default
     *
     * @return string|null
     */
    public function section(string $name, ?string $default = null): ?string
    {
        return $this->tpl->getSections()->get($name) ?? $default;
    }

    public function getData(): array
    {
        return $this->tpl->getData();
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
        return $this->tpl->fetch($name, $data);
    }

    /**
     * Apply multiple functions to variable.
     *
     * @param mixed $subject
     * @param string $functions
     *
     * @return mixed
     */
    public function apply($subject, string $functions)
    {
        $functionsList = explode('|', $functions);

        foreach ($functionsList as $function) {
            if ($this->fnExists($function)) {
                $subject = $this->tpl->getVar($function)($subject);
            } elseif (is_callable($function)) {
                $subject = $function($subject);
            } else {
                throw new RuntimeException(sprintf(
                    'The batch function could not find the "%s" function.',
                    $function
                ));
            }
        }

        return $subject;
    }

    public function fnExists(string $name): bool
    {
        $fn = $this->tpl->getVar($name);
        return (! empty($fn) && is_callable($fn));
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
