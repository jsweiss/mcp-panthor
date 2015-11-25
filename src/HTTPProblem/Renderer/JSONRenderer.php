<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\HTTPProblem\Renderer;

use QL\Panthor\HTTPProblem\HTTPProblem;
use QL\Panthor\HTTPProblem\ProblemRendererInterface;

class JSONRenderer implements ProblemRendererInterface
{
    /**
     * @type int
     */
    private $encodingOptions;

    /**
     * @param int $encodingOptions
     */
    public function __construct($encodingOptions = null)
    {
        if (!is_int($encodingOptions)) {
            $encodingOptions = JSON_PRETTY_PRINT | JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES;
        }

        $this->encodingOptions = $encodingOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function status(HTTPProblem $problem)
    {
        return $problem->status();
    }

    /**
     * {@inheritdoc}
     */
    public function headers(HTTPProblem $problem)
    {
        return [
            'Content-Type' => 'application/problem+json'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function body(HTTPProblem $problem)
    {
        $data = [
            'status' => $problem->status()
        ];

        if ($problem->title()) {
            $data['title'] = $problem->title();
        }

        if (!in_array($problem->type(), [null, 'about:blank'], true)) {
            $data['type'] = $problem->type();
        }

        if ($problem->detail()) {
            $data['detail'] = $problem->detail();
        }

        if ($problem->instance()) {
            $data['instance'] = $problem->instance();
        }

        $data += $problem->extensions();

        return json_encode($data, $this->encodingOptions);
    }
}
