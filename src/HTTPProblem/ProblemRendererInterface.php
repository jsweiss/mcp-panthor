<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\HTTPProblem;

/**
 * Convert a problem into status, headers and body to be rendered to the client.
 */
interface ProblemRendererInterface
{
    /**
     * @param HTTPProblem $problem
     *
     * @return string
     */
    public function status(HTTPProblem $problem);

    /**
     * @param HTTPProblem $problem
     *
     * @return string[]
     */
    public function headers(HTTPProblem $problem);

    /**
     * @param HTTPProblem $problem
     *
     * @return string
     */
    public function body(HTTPProblem $problem);
}
