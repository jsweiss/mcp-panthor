<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Http;

interface CookieEncryptionInterface
{
    /**
     * @param string $unencrypted
     *
     * @return string
     */
    public function encrypt($unencrypted);

    /**
     * @param string $encrypted
     *
     * @return string|null
     */
    public function decrypt($encrypted);
}
