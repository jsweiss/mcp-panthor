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
        $root = __DIR__ . '/../../';

        $composer = $event->getComposer();
        $package = $composer->getPackage();
        $appRoot = $composer->getInstallationManager()->getInstallPath($package);
        $io = $event->getIO();

        // app settings
        // ask IO for this
        $namespace = 'QL\DerptownUSA';


        $cmdBin = sprintf('cp -R -v "%s/bin" "%s/bin"', $root, $appRoot);
        $cmdConfig = sprintf('cp -R -v "%s/configuration" "%s/configuration"', $root, $appRoot);
        $cmdPublic = sprintf('cp -R -v "%s/public" "%s/public"', $root, $appRoot);

        // copy bin/, configuration/, public/
        $io->write('Copying application files');
        exec($cmdBin);
        exec($cmdConfig);
        exec($cmdPublic);

        // sanitize these files:

        // bin/dump-di.dist -> put it at bin/dump-di
        $io->write('Preparing bin/dump-di.dist');
        $dumpDi = file_get_contents($appRoot. '/bin/dump-di.dist');
        $dumpDi = str_replace('{{ application.namespace }}', $namespace);
        file_put_contents($appRoot. '/bin/dump-di', $dumpDi);
        unlink($appRoot. '/bin/dump-di.dist');

        // configuration/bootstrap.php.dist -> put it at configuration/bootstrap.php
        // public/index.php.dist -> put it at public/index.php

        // overwrite composer.json
        // ask user for package name

        $io->write(var_export($root, true));
    }
}
