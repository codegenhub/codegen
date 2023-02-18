<?php


use Symfony\Component\Console\Application;
use Material4\Codegen\App\Commands\{Generate,Live,Rollback};

require_once __DIR__ . '/vendor/autoload.php';

$application = new Application();
$application->add(new Generate());
$application->add(new Rollback());
$application->add(new Live());

$application->run();
