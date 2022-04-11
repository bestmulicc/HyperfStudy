<?php

use Src\Command\StartCommand;
use Src\Config\Config;
use Src\Config\ConfigFactory;
use Symfony\Component\Console\Application;

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

require 'vendor/autoload.php';

$application = new Application();
$config = new ConfigFactory();
$config = $config();
$commands = $config->get('commands');
foreach ($commands as $command){
    $application->add(new $command);
}
$application->run();
