<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Utility;

use QL\Panthor\Exception\Exception;

/**
 * This utility protects against string functions being overridden by mb_*.
 *
 * Protip: Never ever do this! But just in case you do, this class will protect you.
 *
 * @see http://php.net/manual/en/mbstring.overload.php
 */
class ByteString
{
    const ERR_STRLEN = 'Could not determine byte size of string.';

    /**
     * @type bool
     */
    private static $isOverloaded;

    /**
     * @return bool
     */
    private static function isOverloaded()
    {
        if (self::$isOverloaded === null) {
            $config = (int) ini_get('mbstring.func_overload');
            self::$isOverloaded = ($config & 2);
        }

        return self::$isOverloaded;
    }

    /**
     * Get the length of a string in bytes.
     *
     * Proxy for strlen, to protect if strlen is overridden by mb_strlen
     *
     * @param mixed $input
     *
     * @return int
     */
    public static function strlen($input)
    {
        if (!is_string($input)) {
            return 0;
        }

        if (function_exists('\mb_strlen') && self::isOverloaded()) {
            $len = \mb_strlen($input, '8bit');

        } else {
            $len = \strlen($input);
        }

        if (is_int($len)) {
            return $len;
        }

        throw new Exception(self::ERR_STRLEN);
    }

    /**
     * Get part of a string, offset in bytes.
     *
     * Proxy for substr, to protect if substr is overridden by mb_substr
     *
     * Warning: mb_strlen and mb_strcut behave slightly differently than strlen.
     * If an offset is invalid, or the input string is empty, mb_* functions will return "" (empty string) instead of false.
     *
     * @param mixed $input
     * @param int $start
     * @param int $length
     *
     * @return int
     */
    public static function substr($input, $start, $length = null)
    {
        if (!is_string($input)) {
            return '';
        }

        $args = func_get_args();

        if (function_exists('\mb_strcut') && self::isOverloaded()) {
            $args[] = '8bit';
            $cut = call_user_func_array('\mb_strcut', $args);

        } else {
            $cut = call_user_func_array('\substr', $args);
        }

        return $cut;
    }
}
