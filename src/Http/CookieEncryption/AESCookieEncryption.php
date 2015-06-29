<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Http\CookieEncryption;

use MCP\Crypto\Exception\CryptoException;
use MCP\Crypto\Package\AESPackage;
use QL\Panthor\Http\CookieEncryptionInterface;

/**
 * This uses mcrypt AES encryption.
 *
 * It is backwards compatible with mcp-crypto 1.0
 */
class AESCookieEncryption implements CookieEncryptionInterface
{
    /**
     * @type AESPackage
     */
    private $encryption;

    /**
     * @param AESPackage $encryption
     */
    public function __construct(AESPackage $encryption)
    {
        $this->encryption = $encryption;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($unencrypted)
    {
        return $this->encryption->encrypt($unencrypted);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($encrypted)
    {
        try {
            return $this->encryption->decrypt($encrypted);
        } catch (CryptoException $ex) {
            return null;
        }
    }
}
