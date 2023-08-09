<?php


use Symfony\Component\Console\Application;
use Codegenhub\App\Commands\{Generate, Live, Rollback, Test};

require_once __DIR__ . '/../../../vendor/autoload.php';

$application = new Application();
$application->add(new Generate());
$application->add(new Rollback());
$application->add(new Live());
$application->add(new Test());

$application->run();
