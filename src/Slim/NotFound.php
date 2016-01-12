<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Slim;

use Slim\Slim;

/**
 * Simple proxy for Slim::notFound
 */
class NotFound
{
    /**
     * @type Slim
     */
    private $slim;

    /**
     * @param Slim $slim
     */
    public function __construct(Slim $slim)
    {
        $this->slim = $slim;
    }

    /**
     * @see Slim::notFound
     */
    public function __invoke()
    {
        return $this->slim->notFound();
    }
}
