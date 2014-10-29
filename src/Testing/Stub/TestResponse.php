<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Testing\Stub;

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
