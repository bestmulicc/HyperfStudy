<?php

namespace Src\Config;

use Symfony\Component\Finder\Finder;

//配置文件工厂，读取config文件中的所有配置文件，将其实例化成一个Config对象
class ConfigFactory
{
    public function __invoke()
    {
        $basePath = BASE_PATH.'/config';
        $configFile = $this->readConfig($basePath.'/config.php');
//        var_dump($configFile);
        $autoloadConfig = $this->readPath([$basePath.'/autoload']);
//        var_dump($autoloadConfig);
        $configs = array_merge_recursive($configFile, $autoloadConfig);
//        var_dump($configs);
        return new Config($configs);
    }

    protected function readConfig(string $string)
    {
        $config = require $string;
        if (! is_array($config)){
            return [];
        }
        return $config;
    }

    protected function readPath(array $dirs):array
    {
        $config = [];
        $finder = new Finder();
        $finder->files()->in($dirs)->name('*.php');
        foreach ($finder as $fileInfo){
            $key = $fileInfo->getBasename('.php');
            $value = require $fileInfo->getRealPath();
            $config[$key] = $value;
        }
        return $config;
    }
}