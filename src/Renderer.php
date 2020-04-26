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
 * and template folders.
 */
class Renderer
{
    /** @var string */
    public const DEFAULT_FILE_EXT = 'tpl';

    protected string $fileExt;

    protected array $folders = [];

    protected Data $data;

    public function __construct(
        array $folders,
        string $fileExt = self::DEFAULT_FILE_EXT
    ) {
        $this->folders = $folders;
        $this->fileExt = $fileExt ?: self::DEFAULT_FILE_EXT;
        $this->data = new Data();
    }

    /**
     * @param  array $data
     * @param  null|string|array $templates
     *
     * @return $this
     */
    public function withData(array $data, $templates = null): self
    {
        $this->data->add($data, $templates);
        return $this;
    }

    public function createTemplateByName(string $name): Template
    {
        return new Template($this, $name);
    }

    public function render(string $name, array $data = []): string
    {
        return $this->createTemplateByName($name)->render($data);
    }

    public function getFileExt(): string
    {
        return $this->fileExt;
    }

    public function getData(?string $template = null): array
    {
        return $this->data->get($template);
    }

    public function getFolders(): array
    {
        return $this->folders;
    }

    public function getFolderPathByAlias(string $name): string
    {
        if (! isset($this->folders[$name])) {
            throw new RuntimeException(
                'The template folder "' . $name . '" was not found.'
            );
        }

        return $this->folders[$name];
    }
}
