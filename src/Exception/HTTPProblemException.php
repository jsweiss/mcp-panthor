<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Exception;

use QL\Panthor\HTTPProblem\HTTPProblem;

class HTTPProblemException extends Exception
{
    /**
     * @type HTTPProblem
     */
    private $problem;

    /**
     * @param int $status
     * @param string $detail
     * @param array $extensions
     */
    public function __construct($status, $detail, array $extensions = [])
    {
        $this->problem = new HTTPProblem($status, $detail, $extensions);

        parent::__construct($detail);
    }

    /**
     * @return HTTPProblem
     */
    public function problem()
    {
        return $this->problem;
    }
}
