<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Http;

use ArrayIterator;
use JsonSerializable;
use MCP\Crypto\AES;
use Slim\Http\Cookies;
use Slim\Slim;

/**
 * Hi! You guys want some cookies?
 *
 * @see https://www.youtube.com/watch?v=m1LUnPTlpbQ
 *
 * This is a replacement for Slim cookies that encapsulates all cookie management and encrypts them with our
 * protocol.
 */
class EncryptedCookies extends Cookies
{
    /**
     * @type AES
     */
    private $encryption;

    /**
     * @type Slim
     */
    private $slim;

    /**
     * @param Slim $slim
     * @param AES $encryption
     */
    public function __construct(Slim $slim, AES $encryption)
    {
        $this->slim = $slim;
        $this->encryption = $encryption;
    }

    /**
     * Used by slim to render out cookies. Never retrieve response cookies within the application!
     */
    public function getResponseCookie($key)
    {
        if (!$cookie = parent::get($key)) {
            return null;
        }

        $value = array_key_exists('value', $cookie) ? $cookie['value'] : null;

        // auto stringify
        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;

        // auto jsonify
        } elseif (is_array($value) || $value instanceof JsonSerializable) {
            $value = json_encode($value);
        }

        $value = ($value) ? $this->encryption->encrypt($value) : null;
        $cookie['value'] = $value;

        return $cookie;
    }

    /**
     * {inheritdoc}
     */
    public function getIterator()
    {
        $sanitized = $this->all();

        array_walk($sanitized, function(&$v, $key) {
            $v = $this->getResponseCookie($key);
        });

        return new ArrayIterator($sanitized);
    }

    /**
     * Convenience method to centralize cookie handling (also so we dont have to pass Slim\Slim around as a dependency)
     *
     * @see Slim::deleteCookie
     */
    public function deleteCookie($name)
    {
        return call_user_func_array([$this->slim, __FUNCTION__], func_get_args());
    }

    /**
     * Convenience method to centralize cookie handling (also so we dont have to pass Slim\Slim around as a dependency)
     *
     * @see Slim::getCookie
     */
    public function getCookie($name)
    {
        if ($value = $this->slim->getCookie($name)) {
            $decrypted = $this->encryption->decrypt($value);
            if (is_string($decrypted)) {
                return $decrypted;
            }

            $this->deleteCookie($name);
        }
    }

    /**
     * Convenience method to centralize cookie handling (also so we dont have to pass Slim\Slim around as a dependency)
     *
     * @see Slim::setCookie
     */
    public function setCookie()
    {
        return call_user_func_array([$this->slim, __FUNCTION__], func_get_args());
    }
}
