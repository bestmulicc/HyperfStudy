<?php

namespace Src\Config;

class ConfigFactory
{
    public function __invoke()
    {
        $basePath = BASE_PATH.'/config';
        $configFile = $this->readConfig($basePath.'/config.php');
        var_dump($configFile);exit();
    }

    protected function readConfig(string $string)
    {
        $config = require $string;
        if (! is_array($config)){
            return [];
        }
        return $config;
    }
}