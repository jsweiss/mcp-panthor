<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Http;

use Mockery;
use PHPUnit_Framework_TestCase;
use QL\Panthor\Testing\Stub\StringableStub;

class EncryptedCookiesTest extends PHPUnit_Framework_TestCase
{
    public $slim;
    public $encryption;

    public function setUp()
    {
        $this->slim = Mockery::mock('Slim\Slim');
        $this->encryption = Mockery::mock('MCP\Crypto\AES');
    }

    public function testDeleteCookieParamsPassThroughToSlim()
    {
        $this->slim
            ->shouldReceive('deleteCookie')
            ->with('name', null, '*.domain.com')
            ->once();

        $cookies = new EncryptedCookies($this->slim, $this->encryption);

        $cookies->deleteCookie('name', null, '*.domain.com');
    }

    public function testSetCookieParamsPassThroughToSlim()
    {
        $this->slim
            ->shouldReceive('setCookie')
            ->with('name', 'testvalue', 0, '/page', '.domain.com')
            ->once();

        $cookies = new EncryptedCookies($this->slim, $this->encryption);

        $cookies->setCookie('name', 'testvalue', 0, '/page', '.domain.com');
    }

    public function testGetCookieDecryptsCookieFromSlim()
    {
        $this->encryption
            ->shouldReceive('decrypt')
            ->with('encryptedvalue')
            ->andReturn('decryptedvalue');

        $this->slim
            ->shouldReceive('getCookie')
            ->with('name')
            ->andReturn('encryptedvalue');

        $cookies = new EncryptedCookies($this->slim, $this->encryption);

        $actual = $cookies->getCookie('name');

        $this->assertSame('decryptedvalue', $actual);
    }

    public function testInvalidCookieDeletesByDefaultAndReturnsNull()
    {
        $this->encryption
            ->shouldReceive('decrypt')
            ->with('encryptedvalue')
            ->andReturnNull();

        $this->slim
            ->shouldReceive('getCookie')
            ->with('name')
            ->andReturn('encryptedvalue');

        $this->slim
            ->shouldReceive('deleteCookie')
            ->with('name')
            ->once();

        $cookies = new EncryptedCookies($this->slim, $this->encryption);

        $actual = $cookies->getCookie('name');
        $this->assertNull($actual);
    }

    public function testResponseCookiesAreEncryptedWhenRetrieved()
    {
        $this->encryption
            ->shouldReceive('encrypt')
            ->with('cookievalue1')
            ->andReturn('encrypted-cookievalue1');
        $this->encryption
            ->shouldReceive('encrypt')
            ->with('cookievalue2')
            ->andReturn('encrypted-cookievalue2');

        $cookies = new EncryptedCookies($this->slim, $this->encryption);

        // prime the data
        $cookies['cookie1'] = ['value' => 'cookievalue1'];
        $cookies['cookie2'] = ['value' => 'cookievalue2'];
        $cookies['cookie3'] = ['value' => null];

        $responseCookies = [];
        foreach ($cookies as $name => $value) {
            $responseCookies[$name] = $value['value'];
        }

        $expected = [
            'cookie1' => 'encrypted-cookievalue1',
            'cookie2' => 'encrypted-cookievalue2',
            'cookie3' => null
        ];

        $this->assertSame($expected, $responseCookies);
    }

    public function testCookieIsAutomaticallyJsonSerialized()
    {
        $data = ['bing', 'bong'];
        $dataJsonified = '["bing","bong"]';

        $this->encryption
            ->shouldReceive('encrypt')
            ->with($dataJsonified)
            ->andReturn('encrypted-value')
            ->once();

        $cookies = new EncryptedCookies($this->slim, $this->encryption);

        // prime the data
        $cookies['cookie1'] = ['value' => $data];

        $cookie = $cookies->getResponseCookie('cookie1');
        $expected = 'encrypted-value';
        $this->assertSame($expected, $cookie['value']);
    }

    public function testCookieIsAutomaticallyStringified()
    {
        $this->encryption
            ->shouldReceive('encrypt')
            ->with('stringified')
            ->andReturn('encrypted-value')
            ->once();

        $cookies = new EncryptedCookies($this->slim, $this->encryption);

        // prime the data
        $cookies['cookie1'] = ['value' => new StringableStub('stringified')];

        $cookie = $cookies->getResponseCookie('cookie1');
        $expected = 'encrypted-value';
        $this->assertSame($expected, $cookie['value']);
    }

    public function testMissingCookieReturnsNull()
    {
        $cookies = new EncryptedCookies($this->slim, $this->encryption);

        $cookie = $cookies->getResponseCookie('cookie1');
        $this->assertSame(null, $cookie);
    }
}
