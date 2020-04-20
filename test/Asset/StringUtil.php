<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

namespace BitFrame\Renderer\Test\Asset;

use function strtoupper;
use function strtolower;

class StringUtil
{
    public static function uppercase(string $input): string
    {
        return strtoupper($input);
    }

    public function lowercase(string $input): string
    {
        return strtolower($input);
    }

    public static function escape(string $input): string
    {
        static $flags;

        if (! isset($flags)) {
            $flags = ENT_QUOTES | (defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0);
        }

        return htmlspecialchars($input, $flags, 'UTF-8');
    }
}
