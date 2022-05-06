<?php

namespace Src\Server;

use Psr\Container\ContainerInterface;

class ServerFactory
{
    /**
     * @var null|ServerConfig
     */
    protected $serverConfig;

    /**
     * @var Server
     */
    protected $server;

//    /**
//     * @var ContainerInterface
//     */
//    protected $container;

//    public function __construct(ContainerInterface $container)
//    {
//        $this->container = $container;
//    }
    public function configure(array $configs)
    {
        $this->serverConfig = $configs;
//        var_dump($this->serverConfig);
        $this->getServer()->init($this->serverConfig);
    }

    public function getServer():Server
    {
        if (! isset($this->server)) {
            $this->server = new Server();
        }
        return $this->server;
    }
}