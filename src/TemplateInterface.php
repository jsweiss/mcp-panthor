<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor;

interface TemplateInterface
{
    /**
     * Render the template with context data.
     *
     * @return null
     */
    public function render(array $context = []);
}
