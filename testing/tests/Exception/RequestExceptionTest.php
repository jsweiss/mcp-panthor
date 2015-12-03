<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Exception;

use PHPUnit_Framework_TestCase;

class RequestExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException QL\Panthor\Exception\Exception
     */
    public function test()
    {
        throw new RequestException;
    }
}
