<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Bootstrap;

if (!$container = @include __DIR__ . '/../configuration/bootstrap.php') {
    http_response_code(500);
    echo "The application failed to start.\n";
    exit;
};

// Application
$app = $container->get('slim');

// Custom application logic here

$app->run();
