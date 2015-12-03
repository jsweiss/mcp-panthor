<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor;

interface MiddlewareInterface
{
    /**
     * The primary action of this middleware. Any return from this method is ignored.
     *
     * @return null
     */
    public function __invoke();
}
