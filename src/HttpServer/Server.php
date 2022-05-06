<?php

namespace Src\HttpServer;


use FastRoute\RouteCollector;
use Hyperf\HttpMessage\Server\Connection\SwooleConnection;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Src\Config\Config;
use Src\Config\ConfigFactory;
use Src\Dispatcher\HttpRequestHandler;
use Src\HttpServer\Contract\CoreMiddlewareInterface;
use Src\HttpServer\Exception\Handler\HttpExceptionHandler;
use Src\HttpServer\Router\Dispatched;
use Src\HttpServer\Router\DispatcherFactory;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use function FastRoute\simpleDispatcher;
use FastRoute\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Src\Exception\ExceptionHandlerDispatcher;

class Server
{
    /**
     * @var Dispatcher;
     */
    protected $dispatcher;

    /**
     * @var CoreMiddlewareInterface
     */
    protected $coreMiddleware;

    /**
     * @var DispatcherFactory
     */
    protected $dispatcherFactory;

    /**
     * @var ExceptionHandlerDispatcher
     */
    protected $exceptionHandlerDispatcher;

    /**
     * @var array
     */
    protected $exceptionHandlers;
    //储存从配置文件读取出来的中间件的地方
    protected $globalMiddlewares;

    public function __construct(Router\DispatcherFactory $dispatcherFactory, ExceptionHandlerDispatcher $exceptionHandlerDispatcher)
    {
        $this->dispatcherFactory = $dispatcherFactory;
        $this->dispatcher = $this->dispatcherFactory->getDispatcher('http');
        $this->exceptionHandlerDispatcher = $exceptionHandlerDispatcher;
    }

    public function initCoreMiddleware()
    {
        //调用ConfigFactory中的invoke方法，返回一个Config对象
        $config = (new ConfigFactory())();
        //调用Config对象中的get方法，获取配置文件中的全局middlewares信息
        $this->globalMiddlewares = $config->get('middlewares');
        $this->exceptionHandlers = $config->get('exceptions',$this->getDefaultExceptionHandler());
        $this->coreMiddleware = new CoreMiddleware($this->dispatcherFactory);
    }

    public function onRequest(SwooleRequest $request,SwooleResponse $response)
    {
        try {
            //初始化Psr7的Request，Response对象
            /** @var Psr\Http\Message\RequestInterface $psr7Request */
            /** @var Psr\Http\Message\ResponseInterface $psr7Response */
            [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);

            //读取请求中的uri，httpmethod，与本地路由匹配，增加状态码并返回
            $psr7Request = $this->coreMiddleware->dispatch($psr7Request);

            $httpMethod = $psr7Request->getMethod();
            $uri = $psr7Request->getUri()->getPath();
            //获取全局中间件信息
            $middlewares = $this->globalMiddlewares ?? [];

            //$dispatched获取处理过后的请求信息，包含状态码以及handler方法
            $dispatched = $psr7Request->getAttribute(Dispatched::class);

            //判断是否找到路由，并检索局部中间件信息，合并为$middlewares数组
            if ($dispatched instanceof Dispatched && $dispatched->isFound()) {
                $registeredMiddlewares = MiddlewareManager::get($uri, $httpMethod) ?? [];
                $middlewares = array_merge($middlewares, $registeredMiddlewares);
            }

            //执行所有匹配的中间件
            $requestHandler = new HttpRequestHandler($middlewares, $this->coreMiddleware);

            $psr7Response = $requestHandler->handle($psr7Request);

            /*
             * Headers
             */
            foreach ($psr7Response->getHeaders() as $key => $value) {
                $response->header($key, implode(';', $value));
            }
            /*
             * Status code
             */
            $response->status($psr7Response->getStatusCode());
            $response->end($psr7Response->getBody()->getContents());
        } catch (\Throwable $throwable) {
            $psr7Response = $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
            $response->status($psr7Response->getStatusCode());
            $response->end($psr7Response->getBody()->getContents());
        }
    }

    protected function initRequestAndResponse(SwooleRequest $request,SwooleResponse $response):array
    {
//        Context::set(ResponseInterface::class,$psr7Response = new Psr7Response());
//        Context::set(ServerRequestInterface::class,$psr7Request = Psr7Request::loadFromSwooleRequest($request));
//        return [$psr7Request,$psr7Response];
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response());

        if ($request instanceof ServerRequestInterface) {
            $psr7Request = $request;
        } else {
            $psr7Request = Psr7Request::loadFromSwooleRequest($request);
            $psr7Response->setConnection(new SwooleConnection($response));
        }

        Context::set(ServerRequestInterface::class, $psr7Request);
        return [$psr7Request, $psr7Response];
    }

    protected function getDefaultExceptionHandler():array
    {
        return [
            HttpExceptionHandler::class,
        ];
    }
    //        var_dump($method,$path);
//      1,从routes.php中获取
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


//          3，dispatcher
//        $routes = $this->config->get('routes');
//        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) use ($routes) {
//            foreach ($routes as $route){
//                [$httpMethod , $path, $handler] = $route;
//                $routeCollector->addRoute($httpMethod , $path, $handler);
//            }
//        });
//        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
//        switch ($routeInfo[0]) {
//            case Dispatcher::NOT_FOUND:
//                $response->status(404);
//                $response->end("NOT FOUND");
//                break;
//            case Dispatcher::METHOD_NOT_ALLOWED:
//                $allowedMethods = $routeInfo[1];
//                $response->status(405);
//                $response->header('Method-Allows',implode(',',$allowedMethods));
//                $response->end();
//                break;
//            case Dispatcher::FOUND:
//                $handler = $routeInfo[1];
//                $vars = $routeInfo[2];
//                [$controller, $action] = $handler;
//                $instance = new $controller();
//                $result = $instance->$action(...$vars);
//                $response->end($result);
//                break;
//        }
}