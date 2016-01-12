<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
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
