<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Http\CookieEncryption;

use MCP\Crypto\Exception\CryptoException;
use MCP\Crypto\Package\AESPackage;
use Mockery;
use PHPUnit_Framework_TestCase;

class AESCookieEncryptionTest extends PHPUnit_Framework_TestCase
{
    public $aes;

    public function setUp()
    {
        $this->aes = Mockery::mock(AESPackage::CLASS);
    }

    public function testEncryption()
    {
        $this->aes
            ->shouldReceive('encrypt')
            ->with('testvalue')
            ->andReturn('encryptedvalue')
            ->once();

        $cookies = new AESCookieEncryption($this->aes);
        $actual = $cookies->encrypt('testvalue');

        $this->assertSame('encryptedvalue', $actual);
    }

    public function testDecryption()
    {
        $this->aes
            ->shouldReceive('decrypt')
            ->with('testvalue')
            ->andReturn('decryptedvalue')
            ->once();

        $cookies = new AESCookieEncryption($this->aes);
        $actual = $cookies->decrypt('testvalue');

        $this->assertSame('decryptedvalue', $actual);
    }

    public function testDecryptionErrorIsCaught()
    {
        $this->aes
            ->shouldReceive('decrypt')
            ->with('testvalue')
            ->andThrow(new CryptoException)
            ->once();

        $cookies = new AESCookieEncryption($this->aes);
        $actual = $cookies->decrypt('testvalue');

        $this->assertSame(null, $actual);
    }
}
