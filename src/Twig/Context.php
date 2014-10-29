<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Twig;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * A global container for context. This allows any middleware or controller to add data to context,
 * which is always passed to the final twig template upon rendering.
 */
class Context implements Countable, IteratorAggregate
{
    /**
     * @type array
     */
    private $context;

    /**
     * @param array $initialContext
     */
    public function __construct(array $initialContext = [])
    {
        $this->context = $initialContext;
    }

    /**
     * Merge additional context into the existing data.
     *
     * @var array $context
     * @return null
     */
    public function addContext(array $context)
    {
        $this->context = array_merge_recursive($this->context, $context);
    }

    /**
     * Get the template context. If no key is provided, all context data is returned.
     *
     * @param string|null $key
     *
     * @return array
     */
    public function get($key = null)
    {
        if (func_num_args() > 0) {
            return isset($this->context[$key]) ? $this->context[$key] : null;
        }

        return $this->context;
    }

    /**
     * @see Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->context);
    }

    /**
     * @see IteratorAggregate
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->context);
    }
}