<?php

call_user_func(static function () {

    // Import global variables (command line arguments)
    global $argv;

    // Auto-load classes and libraries (see "composer.json" file)
    require dirname(__FILE__) . '/vendor/autoload.php';

    // Launch the application
    $application = new \SchamsNet\AwsIamSignedRequests\Core\Bootstrap($argv, dirname(__FILE__));
    $application->run();
});
