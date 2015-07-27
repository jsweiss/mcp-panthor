<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Exception;

use Mockery;
use PHPUnit_Framework_TestCase;

class NotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException QL\Panthor\Exception\Exception
     */
    public function test()
    {
        throw new NotFoundException;
    }
}
