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

class ProtectErrorHandlerMiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function testErrorHandlerIsReset()
    {
        $slim = Mockery::mock(Slim::CLASS);
        $slim
            ->shouldReceive('call')
            ->once();

        $handler = function() {};

        $middleware = new ProtectErrorHandlerMiddleware($handler);
        $middleware->setNextMiddleware($slim);

        $existingHandler = set_error_handler(null);

        $middleware->call();

        $appHandler = set_error_handler($existingHandler);

        $this->assertSame($handler, $appHandler);

    }
}
