<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Http\CookieEncryption;

use QL\Panthor\Encryption\LibsodiumSymmetricCrypto;
use QL\Panthor\Exception\CryptoException;
use QL\Panthor\Http\CookieEncryptionInterface;

/**
 * Encrypts payload using libsodium authenticated symmetric encryption.
 *
 * The payload is then encoded with uri-safe base64.
 */
class LibsodiumCookieEncryption implements CookieEncryptionInterface
{
    /**
     * @type LibsodiumSymmetricCrypto
     */
    private $crypto;

    /**
     * @param string secret
     */
    public function __construct(LibsodiumSymmetricCrypto $crypto)
    {
        $this->crypto = $crypto;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($unencrypted)
    {
        try {
            $encrypted = $this->crypto->encrypt($unencrypted);
        } catch (CryptoException $ex) {
            return null;
        }

        return $this->safeEncode($encrypted);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($encrypted)
    {
        $payload = $this->safeDecode($encrypted);
        if (!$payload) {
            return null;
        }

        try {
            $unencrypted = $this->crypto->decrypt($payload);
        } catch (CryptoException $ex) {
            return null;
        }

        return $unencrypted;
    }

    /**
     * @param string $message
     *
     * @return string|null
     */
    private function safeEncode($message)
    {
        $encoded = base64_encode($message);
        $uriSafe = str_replace(['+', '/'], ['-', '_'], $encoded);

        return rtrim($uriSafe, '=');
    }

    /**
     * @param string $message
     *
     * @return string|null
     */
    private function safeDecode($message)
    {
        $message = str_replace(['-', '_'], ['+', '/'], $message);

        $decoded = base64_decode($message, true);
        if (!is_string($decoded)) {
            return null;
        }

        return $decoded;
    }
}
