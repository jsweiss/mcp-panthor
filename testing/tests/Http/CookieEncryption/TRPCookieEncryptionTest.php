<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Http\CookieEncryption;

use MCP\Crypto\Exception\CryptoException;
use MCP\Crypto\Package\TamperResistantPackage;
use Mockery;
use PHPUnit_Framework_TestCase;

class TRPCookieEncryptionTest extends PHPUnit_Framework_TestCase
{
    public $trp;

    public function setUp()
    {
        $this->trp = Mockery::mock(TamperResistantPackage::CLASS);
    }

    public function testEncryption()
    {
        $this->trp
            ->shouldReceive('encrypt')
            ->with('testvalue')
            ->andReturn('encryptedvalue')
            ->once();

        $cookies = new TRPCookieEncryption($this->trp);
        $actual = $cookies->encrypt('testvalue');

        $this->assertSame('encryptedvalue', $actual);
    }

    public function testDecryption()
    {
        $this->trp
            ->shouldReceive('decrypt')
            ->with('testvalue')
            ->andReturn('decryptedvalue')
            ->once();

        $cookies = new TRPCookieEncryption($this->trp);
        $actual = $cookies->decrypt('testvalue');

        $this->assertSame('decryptedvalue', $actual);
    }

    public function testDecryptionErrorIsCaught()
    {
        $this->trp
            ->shouldReceive('decrypt')
            ->with('testvalue')
            ->andThrow(new CryptoException)
            ->once();

        $cookies = new TRPCookieEncryption($this->trp);
        $actual = $cookies->decrypt('testvalue');

        $this->assertSame(null, $actual);
    }
}
