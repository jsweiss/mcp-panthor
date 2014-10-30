<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Testing;

use Mockery;

class Spy
{
    /**
     * @type mixed
     */
    private $captured;

    /**
     * Invoke this class with a value to store a value. To retrieve it, invoke the class with no arguments.
     *
     * @param mixed|null $captured
     *
     * @return mixed|null
     */
    public function __invoke($captured = null)
    {
        if (func_num_args() === 0) {
            return $this->captured;
        }

        $this->captured = $captured;
    }
}
