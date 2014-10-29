<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Twig;

use InvalidArgumentException;
use Twig_Environment;
use Twig_Template;

/**
 * A simple proxy for twig to lazy load templates and allow incremental context loading.
 *
 * This can be passed to one or more "processors", which can add or modify context data that is then
 * passed to the twig template when rendered.
 *
 * @method string render(string $name, array $context = [])
 */
class LazyTwig
{
    /**
     * @type Twig_Environment
     */
    private $environment;

    /**
     * @type Context
     */
    private $context;

    /**
     * @type Twig_Template|null
     */
    private $twig;

    /**
     * Relative path to the template
     *
     * @type string|null
     */
    private $template;

    /**
     * The relative path the template itself is optional and may be specified later.
     *
     * @param Twig_Environment $environment
     * @param Context $context
     * @param string|null $template
     */
    public function __construct(Twig_Environment $environment, Context $context, $template = null)
    {
        $this->environment = $environment;
        $this->context = $context;
        $this->template = $template;
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ($name === 'render') {
            if (count($arguments) > 0) {
                $this->context()->addContext(array_shift($arguments));
            }

            return $this->lazy()->render($this->context()->get());
        }

        return call_user_func_array([$this->lazy(), $name], $arguments);
    }

    /**
     * Get the template context.
     *
     * @return Context
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * Set the template path.
     *
     * @param string $template
     *
     * @return null
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return Twig_Template
     */
    private function lazy()
    {
        if ($this->twig === null) {
            if (!$this->template) {
                throw new InvalidArgumentException('The template file must be specified.');
            }

            $this->twig = $this->environment->loadTemplate($this->template);
        }

        return $this->twig;
    }
}
