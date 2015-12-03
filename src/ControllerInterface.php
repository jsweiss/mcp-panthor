<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor;

interface ControllerInterface
{
    /**
     * The primary action of this controller. Any return from this method is ignored.
     *
     * @return null
     */
    public function __invoke();
}
