<?php

namespace Src\Server;

use Src\HttpServer\Router\DispatcherFactory;
use Swoole\Coroutine\Server as SwooleCoServer;
use Swoole\Server as SwooleServer;
use Swoole\Http\Server as SwooleHttpServer;

class Server implements ServerInterface
{
    /**
     * @var SwooleServer
     */
    protected $server;

    /**
     * @var array
     */
    protected $onRequestCallbacks = [];

    public function init(array $config): ServerInterface
    {
        foreach ($config['servers'] as $server){
            $this->server =
                new SwooleHttpServer($server['host'],$server['port'],$server['type'],$server['sock_type']);
            $this->registerSwooleEvents($server['callbacks']);
            break;
        }
        return $this;
    }

    public function start()
    {
        $this->getServer()->start();
    }

    public function getServer()
    {
        return $this->server;
    }

    protected function registerSwooleEvents(array $callbacks)
    {
        foreach ($callbacks as $swooleEvent => $callback) {
            list($class,$method) = $callback;
            if($class === \Src\HttpServer\Server::class){
                $instance = new $class(new DispatcherFactory());
            } else {
                $instance = new $class();
            }
            $this->server->on($swooleEvent, [$instance,$method]);
        }
    }
}