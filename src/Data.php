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

use function array_merge;

/**
 * Template data store.
 */
class Data
{
    protected array $sharedVars = [];

    protected array $templateSpecificVars = [];

    public function add(array $data, null|string|array $templates = null): self
    {
        if (null === $templates) {
            $this->shareWithAll($data);
        } else {
            $this->shareWithSome($data, (array) $templates);
        }

        return $this;
    }

    public function get(?string $template = null): array
    {
        if (isset($template, $this->templateSpecificVars[$template])) {
            return array_merge($this->sharedVars, $this->templateSpecificVars[$template]);
        }

        return $this->sharedVars;
    }

    private function shareWithAll(array $data): void
    {
        $this->sharedVars = array_merge($this->sharedVars, $data);
    }

    private function shareWithSome(array $data, array $templates): void
    {
        foreach ($templates as $template) {
            $this->templateSpecificVars[$template] = (isset($this->templateSpecificVars[$template]))
                ? array_merge($this->templateSpecificVars[$template], $data)
                : $data;
        }
    }
}
