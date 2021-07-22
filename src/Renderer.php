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

use RuntimeException;

/**
 * Manages templates, global template data (including vars and functions),
 * and template paths.
 */
class Renderer
{
    /** @var string */
    public const DEFAULT_FILE_EXT = 'tpl';

    protected string $fileExt;

    protected Data $data;

    public function __construct(
        protected array $paths = [],
        string $fileExt = self::DEFAULT_FILE_EXT,
    ) {
        $this->fileExt = $fileExt ?: self::DEFAULT_FILE_EXT;
        $this->data = new Data();
    }

    public function createTemplate(string $name, ?Sections $sections = null): Template
    {
        return new Template($name, $this, $sections);
    }

    public function withData(array $data, null|string|array $tplNames = null): self
    {
        $this->data->add($data, $tplNames);
        return $this;
    }

    /**
     * @param string $name
     * @param array $data
     *
     * @return string
     *
     * @throws \Throwable
     */
    public function render(string $name, array $data = []): string
    {
        return $this->createTemplate($name)->render($data);
    }

    public function getFileExt(): string
    {
        return $this->fileExt;
    }

    public function getData(?string $tplName = null): array
    {
        return $this->data->get($tplName);
    }

    public function getPathByName(string $name): string
    {
        if (! isset($this->paths[$name])) {
            throw new RuntimeException(
                'The template folder "' . $name . '" was not found.'
            );
        }

        return $this->paths[$name];
    }

    public function getPaths(): array
    {
        return $this->paths;
    }
}
