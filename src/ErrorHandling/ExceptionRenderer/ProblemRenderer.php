<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionRenderer;

use Exception;
use QL\Panthor\ErrorHandling\ExceptionRendererInterface;
use QL\Panthor\ErrorHandling\SlimRenderingTrait;
use QL\Panthor\Exception\HTTPProblemException;
use QL\Panthor\HTTPProblem\HTTPProblem;
use QL\Panthor\HTTPProblem\ProblemRendererInterface;
use QL\Panthor\HTTPProblem\Renderer\JSONRenderer;

/**
 * Render exception data as HTTP Problem.
 *
 * Defaults to rendering as JSON
 *
 * @see https://tools.ietf.org/html/draft-ietf-appsawg-http-problem
 */
class ProblemRenderer implements ExceptionRendererInterface
{
    use SlimRenderingTrait;

    /**
     * @type ProblemRendererInterface
     */
    private $renderer;

    /**
     * @param ProblemRendererInterface|null $renderer
     */
    public function __construct(ProblemRendererInterface $renderer = null)
    {
        $this->renderer = $renderer ?: new JSONRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function render($status, array $context)
    {
        $problem = null;
        if (isset($context['exception']) && $context['exception'] instanceof HTTPProblemException) {
            $problem = $context['exception']->problem();
        }

        if (!$problem instanceof HTTPProblem) {
            $message = isset($context['message']) ? $context['message'] : 'Unknown error';
            $problem = $this->createProblem($status, $message);
        }

        $status = $this->renderer->status($problem);
        $body = $this->renderer->body($problem);
        $headers = $this->renderer->headers($problem);

        $this->renderResponse($status, $body, $headers);
    }

    /**
     * @param int $status
     * @param string $message
     *
     * @return HTTPProblem
     */
    private function createProblem($status, $message)
    {
        return new HTTPProblem($status, $message);
    }
}
