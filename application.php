<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

use App\Console\Command\WebPageFinderCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new WebPageFinderCommand());
$application->run();
