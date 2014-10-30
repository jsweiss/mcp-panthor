<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Testing\Stub;

class StringableStub
{
    /**
     * @type mixed
     */
    public $output;

    /**
     * @param string $output
     */
    public function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->output;
    }
}
