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

use InvalidArgumentException;

use function is_array;
use function is_string;
use function array_merge;

/**
 * Template data store.
 */
class Data
{
    protected array $sharedVars = [];

    protected array $templateSpecificVars = [];

    /**
     * @param  array $data
     * @param  null|string|array $templates
     * @return $this
     */
    public function add(array $data, $templates = null): self
    {
        if (null === $templates) {
            return $this->shareWithAll($data);
        }

        if (is_array($templates) || is_string($templates)) {
            return $this->shareWithSome($data, (array) $templates);
        }

        throw new InvalidArgumentException(
            'The templates variable must either be null, an array or a string, ' . gettype($templates) . ' given.'
        );
    }

    public function get(?string $template = null): array
    {
        if (isset($template, $this->templateSpecificVars[$template])) {
            return array_merge($this->sharedVars, $this->templateSpecificVars[$template]);
        }

        return $this->sharedVars;
    }

    private function shareWithAll(array $data): self
    {
        $this->sharedVars = array_merge($this->sharedVars, $data);

        return $this;
    }

    private function shareWithSome(array $data, array $templates): self
    {
        foreach ($templates as $template) {
            $this->templateSpecificVars[$template] = (isset($this->templateSpecificVars[$template]))
                ? array_merge($this->templateSpecificVars[$template], $data)
                : $data;
        }

        return $this;
    }
}
