<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Renderer;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

use function explode;
use function count;
use function rtrim;
use function array_merge;
use function is_file;
use function is_callable;
use function extract;
use function ob_get_level;
use function ob_start;
use function ob_get_clean;
use function sprintf;

use const DIRECTORY_SEPARATOR;

/**
 * Manages template data and provides access to template functions.
 */
class Template
{
    /** @var string */
    private const NAME_SEPARATOR = '::';

    /** @var string */
    private const CONTENT_SECTION_KEY = 'content';

    protected Renderer $engine;

    protected string $filePath;

    protected array $data = [];

    public array $sections = [];

    protected ?string $currentSectionName = null;

    protected bool $appendSection = false;

    protected string $layoutName;

    protected array $layoutData;

    public function __construct(Renderer $engine, string $name)
    {
        $this->engine = $engine;
        $this
            ->setFilePathFromName($name)
            ->withData($this->engine->getData($name));
    }

    /**
     * @param array $data
     *
     * @return Template
     */
    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Render the template and layout.
     *
     * @param array  $data
     *
     * @return string
     *
     * @throws Throwable
     */
    public function render(array $data = []): string
    {
        if (! $this->exists()) {
            throw new RuntimeException(
                'The template "' . $this->filePath . '" could not be found at "' . $this->getFilePath() . '".'
            );
        }

        $this->withData($data);
        $content = $this->buffer(
            fn () => extract($this->data, EXTR_SKIP) & include $this->getFilePath()
        );

        if (isset($this->layoutName)) {
            $layout = $this->engine->createTemplateByName($this->layoutName);
            $layout->sections = array_merge($this->sections, [
                self::CONTENT_SECTION_KEY => $content
            ]);
            $content = $layout->render($this->layoutData);
        }

        return $content;
    }

    public function parent(string $name, array $data = []): void
    {
        $this->layoutName = $name;
        $this->layoutData = $data;
    }

    /**
     * Start a new section block.
     *
     * @param string $name
     */
    public function start(string $name): void
    {
        if ($name === self::CONTENT_SECTION_KEY) {
            throw new RuntimeException(sprintf(
                'The section name "%s" is reserved.',
                self::CONTENT_SECTION_KEY
            ));
        }

        if ($this->currentSectionName) {
            throw new RuntimeException(
                'Sections cannot be nested within one another.'
            );
        }

        $this->currentSectionName = $name;

        ob_start();
    }

    /**
     * Start a new append section block.
     *
     * @param  string $name
     */
    public function push(string $name): void
    {
        $this->appendSection = true;

        $this->start($name);
    }

    public function end(): void
    {
        if (null === $this->currentSectionName) {
            throw new RuntimeException(
                'You must start a section before you can stop it.'
            );
        }

        if (! isset($this->sections[$this->currentSectionName])) {
            $this->sections[$this->currentSectionName] = '';
        }

        $this->sections[$this->currentSectionName] = $this->appendSection
            ? $this->sections[$this->currentSectionName] . ob_get_clean()
            : ob_get_clean();

        $this->currentSectionName = null;
        $this->appendSection = false;
    }

    /**
     * Returns the content for a section block.
     *
     * @param string $name
     * @param string $default
     *
     * @return string|null
     */
    public function section(string $name, ?string $default = null): ?string
    {
        return $this->sections[$name] ?? $default;
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
        return $this->engine->render($name, $data);
    }

    /**
     * Apply multiple functions to variable.
     *
     * @param mixed $subject
     * @param string $functions
     *
     * @return mixed
     */
    public function batch($subject, string $functions)
    {
        $functionsList = explode('|', $functions);

        foreach ($functionsList as $function) {
            if ($this->fnExists($function)) {
                $subject = $this->getVar($function)($subject);
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

    public function exists(): bool
    {
        return is_file($this->getFilePath());
    }

    public function fnExists(string $name): bool
    {
        $fn = $this->getVar($name);
        return (! empty($fn) && is_callable($fn));
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $name
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getVar(string $name, $default = null)
    {
        $data = $this->getData();

        return $data[$name] ?? $default;
    }

    /**
     * @return string
     *
     * @throws Throwable
     */
    public function __toString(): string
    {
        return $this->render();
    }

    private function setFilePathFromName(string $name): self
    {
        $chunks = explode(self::NAME_SEPARATOR, $name);
        [$alias, $fileName] = $chunks;

        if (empty($alias) || empty($fileName) || count($chunks) !== 2) {
            throw new InvalidArgumentException(
                'The template name "' . $fileName . '" is not valid. ' .
                'Do not use the folder namespace separator "::" more than once.'
            );
        }

        $this->filePath = rtrim($this->engine->getPathByName($alias), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $fileName . '.'
            . $this->engine->getFileExt();

        return $this;
    }

    private function buffer(callable $wrap) {
        $level = ob_get_level();

        try {
            ob_start();
            $wrap();
            return ob_get_clean();
        } catch (Throwable $e) {}

        while (ob_get_level() > $level) {
            ob_end_clean();
        }

        throw $e;
    }
}
