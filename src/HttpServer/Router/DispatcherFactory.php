<?php

namespace Src\HttpServer\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Src\HttpServer\MiddlewareManager;
use function FastRoute\simpleDispatcher;

class DispatcherFactory
{
    /**
     * @var string[]
     */
    protected $routesFiles = [BASE_PATH.'/config/routes.php'];

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @var Dispatcher[]
     */
    protected $dispatchers = [];

    public function __construct()
    {
        $this->initConfigRoute();
    }


    public function getDispathcer(string $serverName):Dispatcher
    {
        if (! isset($this->dispatchers[$serverName])){
            $this->dispatchers[$serverName] = simpleDispatcher(function (RouteCollector $routeCollector){
                foreach ($this->routes as $route){
                    [$httpMethod , $path, $handler] = $route;
                    if (isset($route[3])){
                        $options = $route[3];
                    }
                    $routeCollector->addRoute($httpMethod , $path, $handler);
                    if (isset($options['middlewares']) && is_array($options['middlewares'])){
                        MiddlewareManager::addMiddlewares($path,$httpMethod,$options['middlewares']);
                    }
                }
            });
        }
        return $this->dispatchers[$serverName];
    }

    public function initConfigRoute()
    {
        foreach ($this->routesFiles as $file){
            if (file_exists($file)){
                $routes = require_once $file;
                $this->routes = array_merge_recursive($this->routes,$routes);
            }
        }
    }

}