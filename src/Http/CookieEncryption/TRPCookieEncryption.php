<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Http\CookieEncryption;

use MCP\Crypto\Exception\CryptoException;
use MCP\Crypto\Package\TamperResistantPackage;
use QL\Panthor\Http\CookieEncryptionInterface;

/**
 * This uses libsodium encryption from mcp-crypto 2.0.
 */
class TRPCookieEncryption implements CookieEncryptionInterface
{
    /**
     * @type TamperResistantPackage
     */
    private $encryption;

    /**
     * @param TamperResistantPackage $encryption
     */
    public function __construct(TamperResistantPackage $encryption)
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
