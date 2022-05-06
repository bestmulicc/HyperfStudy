<?php

use Src\Command\StartCommand;
use Src\Config\Config;
use Src\Config\ConfigFactory;
use Symfony\Component\Console\Application;

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

require BASE_PATH.'/vendor/autoload.php';

$application = new Application();
$config = new ConfigFactory();
//调用一次ConfigFactory，表示执行了ConfigFactory中的invoke魔术方法
$config = $config();
//var_dump($config);
$commands = $config->get('commands');
foreach ($commands as $command){
    if($command === StartCommand::class){
        $application->add(new StartCommand($config));
    } else {
        $application->add(new $command);
    }
}
$application->run();