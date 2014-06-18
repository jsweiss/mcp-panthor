<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Setup;

use Composer\Script\Event;

class PostInstallCommand
{
    public static function derp(Event $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();


        $wd = $composer->getConfig()->get('working-dir');
        $io->write('derp derp');
        $io->write('working dir='. $wd);

        // sanitize these files:

        // bin/dump-di.dist -> put it at bin/dump-di
        // configuration/bootstrap.php.dist -> put it at configuration/bootstrap.php
        // public/index.php.dist -> put it at public/index.php

        // overwrite composer.json
        // ask user for package name
    }
}
