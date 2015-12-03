<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Slim;

use Mockery;
use PHPUnit_Framework_TestCase;
use Slim\Slim;
use Slim\Http\Request;

class NotFoundTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $slim = Mockery::mock(Slim::CLASS);
        $slim
            ->shouldReceive('notFound')
            ->once();

        $notfound = new NotFound($slim);

        $notfound();
    }
}
