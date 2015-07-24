<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use Slim\Http\Response;
use Slim\Slim;

/**
 * Force sending of the response and end the php process.
 *
 * This is copypasta from Slim\Slim::run, as once an error occurs and the application has broken out of Slim's
 * handling context, Slim cannot be made to re-render the response.
 */
trait SlimRenderingTrait
{
    /**
     * @param Slim $slim
     *
     * @return void
     */
    private function forceSendResponse(Slim $slim)
    {
        list($status, $headers, $body) = $slim->response()->finalize();

        $header = $this->headerSetter;

        if (headers_sent() === false) {

            //Send status
            $header(sprintf('HTTP/%s %s', $slim->config('http.version'), Response::getMessageForCode($status)));

            // send headers
            foreach ($headers as $name => $value) {
                $hValues = explode("\n", $value);
                foreach ($hValues as $hVal) {
                    $header(sprintf('%s: %s', $name, $hVal), false);
                }
            }
        }

        // do not set body for HEAD requests
        if ($slim->request->isHead()) {
            return;
        }

        echo $body;
        exit();
    }
}
