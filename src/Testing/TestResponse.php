<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Testing;

use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\Util;

class TestResponse extends Response
{
    /**
     * Return a string representation of the complete http response.
     *
     * @return string
     */
    public function __toString()
    {
        //Fetch status, header, and body
        list($status, $headers, $body) = $this->finalize();
        Util::serializeCookies($headers, $this->cookies, ['cookies.encrypt' => false]);

        $version = '1.1';
        $text = self::getMessageForCode($status);

        return
            sprintf("HTTP/%s %s\n", $version, $text) .
            $this->stringifyHeaders($headers) . "\n" .
            $body;
    }

    /**
     * @param Headers $headers
     * @return string
     */
    private function stringifyHeaders(Headers $headers)
    {
        $response = '';

        foreach ($headers as $name => $value) {
            $hValues = explode("\n", $value);
            foreach ($hValues as $hVal) {
                $response .= "$name: $hVal\n" ;
            }
        }

        return $response;
    }
}
