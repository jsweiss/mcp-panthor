<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Slim;

use Mockery;
use PHPUnit_Framework_TestCase;
use Slim\Slim;
use Slim\Http\Request;

class HaltTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $slim = Mockery::mock(Slim::CLASS);
        $slim
            ->shouldReceive('halt')
            ->with(500, 'b')
            ->once();

        $notfound = new Halt($slim);

        $notfound(500, 'b');
    }
}
