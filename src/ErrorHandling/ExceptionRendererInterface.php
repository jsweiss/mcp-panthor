<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use Exception;

interface ExceptionRendererInterface
{
    /**
     * Render a exception data to the response.
     *
     * @param int $status
     * @param array $context
     *
     * @return void
     */
    public function render($status, array $context);
}
