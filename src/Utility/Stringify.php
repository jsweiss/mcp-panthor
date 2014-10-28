<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Utility;

class Stringify
{
    /**
     * @param string $template Valid printf template
     * @param array $parameters
     * @return string
     */
    public static function template($template, array $parameters)
    {
        array_unshift($parameters, $template);
        return call_user_func_array('sprintf', $parameters);
    }

    /**
     * @param array $parameters
     * @return string
     */
    public static function combine(array $parameters)
    {
        return implode('', $parameters);
    }
}
