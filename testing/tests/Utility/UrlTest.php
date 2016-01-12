<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Utility;

use Mockery;
use PHPUnit_Framework_TestCase;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;
use Slim\Route;

class UrlTest extends PHPUnit_Framework_TestCase
{
    private $router;
    private $request;
    private $response;
    private $halt;

    public function setUp()
    {
        $this->router = Mockery::mock(Router::CLASS);
        $this->request = Mockery::mock(Request::CLASS);
        $this->response = Mockery::mock(Response::CLASS);

        $this->halt = function() {};
    }

    public function testCurrentRouteReturnsNullIfNoRouteMatches()
    {
        $this->router
            ->shouldReceive('getCurrentRoute')
            ->andReturnNull();

        $url = new Url($this->router, $this->request, $this->response, $this->halt);

        $this->assertSame(null, $url->currentRoute());
    }

    public function testCurrentRouteReturnsRouteName()
    {
        $this->router
            ->shouldReceive('getCurrentRoute')
            ->andReturn(Mockery::mock(Route::CLASS, [
                'getName' => 'route.name'
            ]));

        $url = new Url($this->router, $this->request, $this->response, $this->halt);

        $this->assertSame('route.name', $url->currentRoute());
    }

    public function testUrlForReturnsEmptyStringIfNone()
    {
        $url = new Url($this->router, $this->request, $this->response, $this->halt);

        $this->assertSame('', $url->urlFor('', ['param1' => '1']));
    }

    public function testUrlGetsRouteAndAppendsQueryString()
    {
        $this->router
            ->shouldReceive('urlFor')
            ->with('route.name', ['param1' => '1'])
            ->andReturn('/path');

        $url = new Url($this->router, $this->request, $this->response, $this->halt);

        $actual = $url->urlFor('route.name', ['param1' => '1'], ['query1' => '2']);
        $this->assertSame('/path?query1=2', $actual);
    }

    public function testAbsoluteUrlGetsRouteAndAppendsQueryString()
    {
        $this->request
            ->shouldReceive('getUrl')
            ->andReturn('http://example.com');
        $this->router
            ->shouldReceive('urlFor')
            ->with('route.name', ['param1' => '1'])
            ->andReturn('/path');

        $url = new Url($this->router, $this->request, $this->response, $this->halt);

        $actual = $url->absoluteUrlFor('route.name', ['param1' => '1'], ['query1' => '2']);
        $this->assertSame('http://example.com/path?query1=2', $actual);
    }

    public function testRedirectForRetrievesRoute()
    {
        $this->request
            ->shouldReceive('getUrl')
            ->andReturn('http://example.com');
        $this->router
            ->shouldReceive('urlFor')
            ->with('route.name', [])
            ->andReturn('/path');

        $headers = Mockery::mock(Headers::CLASS);
        $headers
            ->shouldReceive('set')
            ->with('Location', 'http://example.com/path')
            ->once();
        $this->response->headers = $headers;

        $code = null;
        $this->halt = function($v) use (&$code) {
            $code = $v;
        };

        $url = new Url($this->router, $this->request, $this->response, $this->halt);

        $url->redirectFor('route.name');
        $this->assertSame(302, $code);
    }
}

