<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Templating;

use QL\Panthor\TemplateInterface;

/**
 * Null Template implementation.
 */
class NullTemplate implements TemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(array $context = [])
    {
        return '';
    }
}
