<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2021 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Renderer\Test\Asset;

use function strtoupper;
use function strtolower;
use function defined;
use function htmlspecialchars;

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

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
