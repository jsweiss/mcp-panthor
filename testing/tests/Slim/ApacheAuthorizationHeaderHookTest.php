<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Slim;

use Mockery;
use PHPUnit_Framework_TestCase;

class AuthorizationHeaderHookTest extends PHPUnit_Framework_TestCase
{
    public function testAuthHeaderExists()
    {
        $headers = Mockery::mock('Slim\Helper\Set');
        $headers
            ->shouldReceive('has')
            ->with('Authorization')
            ->andReturn(true);
        $headers
            ->shouldReceive('set')
            ->never();

        $request = Mockery::mock('Slim\Http\Request');
        $request->headers = $headers;

        $slim = Mockery::mock('Slim\Slim', ['request' => $request]);

        $hook = new ApacheAuthorizationHeaderHook;
        $hook($slim);
    }

    public function testFailsGracefullyIfCustomHeaderFunctionReturnsWeirdResponse()
    {
        $headers = Mockery::mock('Slim\Helper\Set');
        $headers
            ->shouldReceive('has')
            ->with('Authorization')
            ->andReturn(false);
        $headers
            ->shouldReceive('set')
            ->never();

        $request = Mockery::mock('Slim\Http\Request');
        $request->headers = $headers;

        $slim = Mockery::mock('Slim\Slim', ['request' => $request]);

        $hook = new ApacheAuthorizationHeaderHook([$this, 'weirdCallback']);
        $hook($slim);
    }

    public function testFailsGracefullyIfAuthHeaderIsMissing()
    {
        $headers = Mockery::mock('Slim\Helper\Set');
        $headers
            ->shouldReceive('has')
            ->with('Authorization')
            ->andReturn(false);
        $headers
            ->shouldReceive('set')
            ->never();

        $request = Mockery::mock('Slim\Http\Request');
        $request->headers = $headers;

        $slim = Mockery::mock('Slim\Slim', ['request' => $request]);

        $hook = new ApacheAuthorizationHeaderHook([$this, 'okCallback']);
        $hook($slim);
    }

    public function testAuthHeaderIsGrabbedFromCustomHeaderSource()
    {
        $headers = Mockery::mock('Slim\Helper\Set');
        $headers
            ->shouldReceive('has')
            ->with('Authorization')
            ->andReturn(false);
        $headers
            ->shouldReceive('set')
            ->with('Authorization', 'derp')
            ->once();

        $request = Mockery::mock('Slim\Http\Request');
        $request->headers = $headers;

        $slim = Mockery::mock('Slim\Slim', ['request' => $request]);

        $hook = new ApacheAuthorizationHeaderHook([$this, 'goodCallback']);
        $hook($slim);
    }

    public function weirdCallback()
    {
        return 'badbadbad';
    }

    public function okCallback()
    {
        return [
            'someheader' => 'val'
        ];
    }

    public function goodCallback()
    {
        return [
            'someheader' => 'val',
            'Authorization' => 'derp'
        ];
    }
}
