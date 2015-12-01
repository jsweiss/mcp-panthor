<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Encryption;

use Encryption;
use QL\Panthor\Exception\CryptoException;

/**
 * This uses libsodium encryption from PECL Libsodium ~1.0
 *
 * @see https://pecl.php.net/package/libsodium
 * @see https://github.com/jedisct1/libsodium-php
 */
class LibsodiumSymmetricCrypto
{
    const NONCE_SIZE_BYTES = 24;

    // 2 * 64 = 128 hexadecimal characters
    const REGEX_FULL_SECRET = '^[A-Fa-f0-9]{128}$';

    const FQDN_LIBSODIUM_EXT = 'libsodium';
    const FQDN_LIBSODIUM_VERSION = '\Sodium\version_string';
    const FQDN_RANDOMBYTES = '\random_bytes';

    /**
     * Setup errors
     */
    const ERR_LIBSODIUM = 'Libsodium extension not found. Please install PECL libsodium ~1.0.';
    const ERR_CSPRNG = 'CSPRNG "random_bytes" not found. Please use PHP 7.0 or install paragonie/random_compat.';
    const ERR_INVALID_SECRET = 'Invalid encryption secret. Secret must be 128 hexadecimal characters.';

    /**
     * Encryption errors
     */
    const ERR_CANNOT_ENCRYPT = 'Invalid type "%s" given. Only scalars can be encrypted.';
    const ERR_ENCRYPT = 'An error occured while encrypting data: %s';
    const ERR_ENCODE = 'An error occured while calculating MAC: %s';

    /**
     * Decryption errors
     */
    const ERR_CANNOT_DECRYPT = 'Invalid type "%s" given. Only strings can be decrypted.';
    const ERR_SIZE = 'Invalid encrypted payload provided.';
    const ERR_DECODE_UNEXPECTED = 'An error occured while verifying MAC: %s';
    const ERR_DECODE = 'Could not verify MAC.';
    const ERR_DECRYPT = 'An error occured while decrypting data: %s';

    /**
     * Misc errors
     */
    const ERR_STRLEN = 'Could not determine byte size of string.';

    /**
     * 128-character hexademical string.
     * 64 - box crypto key
     * 64 - auth key
     *
     * @type string
     */
    private $secret;

    /**
     * @param string $secret
     */
    public function __construct($secret)
    {
        if (!extension_loaded(self::FQDN_LIBSODIUM_EXT)) {
            throw new CryptoException(self::ERR_LIBSODIUM);
        }

        if (!function_exists(self::FQDN_LIBSODIUM_VERSION)) {
            throw new CryptoException(self::ERR_LIBSODIUM);
        }

        if (!function_exists(self::FQDN_RANDOMBYTES)) {
            throw new CryptoException(self::ERR_CSPRNG);
        }

        if (1 !== preg_match(sprintf('#%s#', self::REGEX_FULL_SECRET), $secret)) {
            throw new CryptoException(self::ERR_INVALID_SECRET);
        }

        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($unencrypted)
    {
        if (!is_scalar($unencrypted)) {
            throw new CryptoException(sprintf(self::ERR_CANNOT_ENCRYPT, gettype($unencrypted)));
        }

        $encryptionKey = \Sodium\hex2bin($this->substr($this->secret, 0, 64));
        $authKey = \Sodium\hex2bin($this->substr($this->secret, 64, 64));

        // Generate 24 byte nonce
        $nonce = \random_bytes(self::NONCE_SIZE_BYTES);

        // Encrypt payload
        try {
            $encrypted = \Sodium\crypto_secretbox($unencrypted, $nonce, $encryptionKey);
        } catch (Exception $ex) {
            \Sodium\memzero($encryptionKey);
            \Sodium\memzero($authKey);
            throw new CryptoException(sprintf(self::ERR_ENCRYPT, $ex->getMessage()), $ex->getCode(), $ex);
        }

        // Calculate MAC
        try {
            $mac = \Sodium\crypto_auth($nonce . $encrypted, $authKey);
        } catch (Exception $ex) {
            \Sodium\memzero($encryptionKey);
            \Sodium\memzero($authKey);
            throw new CryptoException(sprintf(self::ERR_ENCODE, $ex->getMessage()), $ex->getCode(), $ex);
        }

        \Sodium\memzero($encryptionKey);
        \Sodium\memzero($authKey);

        // Return appended binary string
        return $nonce . $mac . $encrypted;
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($encrypted)
    {
        if (!$encrypted || !is_string($encrypted)) {
            throw new CryptoException(sprintf(self::ERR_CANNOT_DECRYPT, gettype($encrypted)));
        }

        $encryptionKey = \Sodium\hex2bin($this->substr($this->secret, 0, 64));
        $authKey = \Sodium\hex2bin($this->substr($this->secret, 64, 64));

        // Sanity check size of payload is larger than MAC + NONCE
        // Note: we do not care about character count, only raw byte size so we use strlen instead of mb_strlen
        if ($this->strlen($encrypted) < self::NONCE_SIZE_BYTES + \Sodium\CRYPTO_AUTH_BYTES) {
            throw new CryptoException(self::ERR_SIZE);
        }

        // Split into nonce, mac, and encrypted payload
        $nonce = $this->substr($encrypted, 0, self::NONCE_SIZE_BYTES);
        $mac = $this->substr($encrypted, self::NONCE_SIZE_BYTES, \Sodium\CRYPTO_AUTH_BYTES);
        $encrypted = $this->substr($encrypted, self::NONCE_SIZE_BYTES + \Sodium\CRYPTO_AUTH_BYTES);

        // Verify MAC
        try {
            $isVerified = \Sodium\crypto_auth_verify($mac, $nonce . $encrypted, $authKey);
        } catch (Exception $ex) {
            \Sodium\memzero($encryptionKey);
            \Sodium\memzero($authKey);
            throw new CryptoException(sprintf(self::ERR_DECODE_UNEXPECTED, $ex->getMessage()), $ex->getCode(), $ex);
        }

        if (!$isVerified) {
            \Sodium\memzero($encryptionKey);
            \Sodium\memzero($authKey);
            throw new CryptoException(self::ERR_DECODE);
        }

        // Decrypt authenticated payload
        try {
            $unencrypted = \Sodium\crypto_secretbox_open($encrypted, $nonce, $encryptionKey);
        } catch (Exception $ex) {
            \Sodium\memzero($encryptionKey);
            \Sodium\memzero($authKey);
            throw new CryptoException(sprintf(self::ERR_DECRYPT, $ex->getMessage()), $ex->getCode(), $ex);
        }

        \Sodium\memzero($encryptionKey);
        \Sodium\memzero($authKey);

        return $unencrypted;
    }

    /**
     * Proxy for strlen, to protect if strlen is overridden by mb_strlen
     *
     * @param mixed $input
     *
     * @return int
     */
    private function strlen($input)
    {
        if (!is_string($input)) {
            return 0;
        }

        if (function_exists('\mb_strlen')) {
            $len = \mb_strlen($input, '8bit');

        } else {
            $len = \strlen($input);
        }

        if (is_int($len)) {
            return $len;
        }

        throw new CryptoException(self::ERR_STRLEN);
    }

    /**
     * Proxy for substr, to protect if substr is overridden by mb_substr
     *
     * @param mixed $input
     * @param int $start
     * @param int $length
     *
     * @return int
     */
    private function substr($input, $start, $length = null)
    {
        if (!is_string($input)) {
            return '';
        }

        if (function_exists('\mb_strcut')) {
            $cut = \mb_strcut($input, $start, $length, '8bit');

        } else {
            $cut = \substr($input, $start, $length);
        }

        return $cut;
    }
}
