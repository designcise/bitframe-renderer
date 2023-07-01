<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Renderer;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

use function explode;
use function count;
use function rtrim;
use function array_merge;
use function is_file;
use function extract;
use function ob_get_level;
use function ob_start;
use function ob_get_clean;
use function ob_end_clean;

use const EXTR_SKIP;
use const DIRECTORY_SEPARATOR;

/**
 * Manages template data and provides access to template functions.
 */
class Template
{
    /** @var string */
    private const NAME_SEPARATOR = '::';

    protected string $filePath;

    protected array $data = [];

    private Sections $sections;

    private string $childContent = '';

    public function __construct(
        string $name,
        protected Renderer $engine,
        ?Sections $sections = null,
    ) {
        $this->setFilePathFromName($name)->withData($this->engine->getData($name));
        $this->sections = $sections ?? new Sections();
    }

    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * @param array $data
     *
     * @return string
     *
     * @throws Throwable
     */
    public function render(array $data = []): string
    {
        $this->withData($data);
        $data = $this->data;
        $file = $this->getPath();
        $context = new RenderContext(
            [$this, 'fetch'],
            $data,
            $this->sections,
            $this->childContent
        );
        $content = $this->buffer((
            fn () => extract($data, EXTR_SKIP) & include $file
        )->bindTo($context));

        $parentTpl = $context->getParentTemplate();

        if (! empty($parentTpl)) {
            $parent = $this->engine->createTemplate($parentTpl, $this->sections);
            $parent->setChildContent($content);
            $content = $parent->render($context->getParentData());
        }

        return $content;
    }

    /**
     * Fetch a rendered template.
     *
     * @param string $name
     * @param array $data
     *
     * @return string
     *
     * @throws Throwable
     */
    public function fetch(string $name, array $data = []): string
    {
        return $this->engine->createTemplate($name, $this->sections)->render($data);
    }

    public function exists(): bool
    {
        return is_file($this->getPath());
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getPath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getVar(string $name, mixed $default = null): mixed
    {
        return $this->getData()[$name] ?? $default;
    }

    public function getSections(): Sections
    {
        return $this->sections;
    }

    public function setChildContent(string $content): self
    {
        $this->childContent = $content;

        return $this;
    }

    private function setFilePathFromName(string $name): self
    {
        $chunks = explode(self::NAME_SEPARATOR, $name);
        [$alias, $fileName] = $chunks;

        if (empty($alias) || empty($fileName) || count($chunks) !== 2) {
            throw new InvalidArgumentException('The template name "' . $fileName . '" is not valid. ');
        }

        $this->filePath = rtrim($this->engine->getPathByName($alias), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $fileName . '.'
            . $this->engine->getFileExt();

        if (! $this->exists()) {
            throw new RuntimeException(
                'The template "' . $this->filePath . '" could not be found at "' . $this->getPath() . '".'
            );
        }

        return $this;
    }

    /**
     * @param callable $wrap
     *
     * @return false|string
     *
     * @throws Throwable
     */
    private function buffer(callable $wrap): false|string
    {
        $level = ob_get_level();

        try {
            ob_start();
            $wrap();
            return ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }
    }
}
