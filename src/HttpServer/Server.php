<?php

namespace Src\HttpServer;


use FastRoute\RouteCollector;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Src\Config\Config;
use Src\HttpServer\Contract\CoreMiddlewareInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use function FastRoute\simpleDispatcher;
use FastRoute\Dispatcher;
class Server
{
//    /**
//     * @var Config;
//     */
//    protected $config;
    /**
     * @var Dispatcher;
     */
    protected $dispatcher;

    /**
     * @var CoreMiddlewareInterface
     */
    protected $coreMiddleware;
    public function __construct(Router\DispatcherFactory $dispatcherFactory)
    {
        $this->dispatcher = $dispatcherFactory->getDispathcer('http');
        $this->coreMiddleware = new CoreMiddleware($dispatcherFactory);
    }


    public function onRequest(SwooleRequest $request,SwooleResponse $response)
    {
        //初始化Psr7的Request，Response对象
        /** @var Psr\Http\Message\RequestInterface $psr7Request */
        /** @var Psr\Http\Message\ResponseInterface $psr7Response */
        [$psr7Request,$psr7Response] = $this->initRequestAndResponse($request,$response);

        $httpMethod = $psr7Request->getMethod();
        $uri = $psr7Request->getUri()->getPath();
//        var_dump($method,$path);

//      1,从routes中获取
//        $routes = require BASE_PATH.'/config/routes.php';
//        $result = '';
//        foreach ($routes as $route){
//            [$m,$p,$callback] = $route;
//            if ($m === $method && $p === $path){
//                [$class,$method] = explode('@',$callback);
////                var_dump($class,$method);
//                $class = 'App\\Controller\\'.$class;
//                $instance = new $class();
//                $result = $instance->$method();
//            }
//        }

//      2，从path中获取
//        $paths = explode('/',$path);
//        [,$controller,$method] = $paths;
//        var_dump($controller,$method);
//        $controller = 'App\\Controller\\'.Str::title($controller).'Controller';
//        if(!class_exists($controller)){
//            $response->status(404);
//            $response->end("NOT FOUND!");
//        }
//        $instance = new $controller;
//        if (!method_exists($instance,$method)){
//            $response->status(404);
//            $response->end("NOT FOUND!");
//        }
//        $result = $instance->$method();


//        $routes = $this->config->get('routes');
//        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) use ($routes) {
//            foreach ($routes as $route){
//                [$httpMethod , $path, $handler] = $route;
//                $routeCollector->addRoute($httpMethod , $path, $handler);
//            }
//        });

        $psr7Request = $this->coreMiddleware->dispatch($psr7Request);
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response->status(404);
                $response->end("NOT FOUND");
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $response->status(405);
                $response->header('Method-Allows',implode(',',$allowedMethods));
                $response->end();
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                [$controller, $action] = $handler;
                $instance = new $controller();
                $result = $instance->$action(...$vars);
                $response->end($result);
                break;
        }

    }

    protected function initRequestAndResponse(SwooleRequest $request,SwooleResponse $response):array
    {
        Context::set(ResponseInterface::class,$psr7Response = new Psr7Response());
        Context::set(ServerRequestInterface::class,$psr7Request = Psr7Request::loadFromSwooleRequest($request));
        return [$psr7Request,$psr7Response];
    }
}