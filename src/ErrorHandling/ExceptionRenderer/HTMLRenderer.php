<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling\ExceptionRenderer;

use Exception;
use QL\Panthor\ErrorHandling\ExceptionRendererInterface;
use QL\Panthor\ErrorHandling\SlimRenderingTrait;
use QL\Panthor\TemplateInterface;
use QL\Panthor\Templating\NullTemplate;

class HTMLRenderer implements ExceptionRendererInterface
{
    use SlimRenderingTrait;

    /**
     * @type TemplateInterface
     */
    private $template;

    /**
     * @param TemplateInterface|null $template
     */
    public function __construct(TemplateInterface $template = null)
    {
        $this->template = $template ?: new NullTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function render($status, array $context)
    {
        $headers = [
            'Content-Type' => 'text/html'
        ];

        $rendered = $this->template->render($context);
        $this->renderResponse($status, $rendered, $headers);
    }
}
