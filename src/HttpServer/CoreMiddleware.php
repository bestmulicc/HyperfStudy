<?php

namespace Src\HttpServer;

use FastRoute\Dispatcher;
use \Src\HttpServer\Router\Dispatched;
use \Src\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;
use Src\HttpServer\Contract\CoreMiddlewareInterface;

class CoreMiddleware implements CoreMiddlewareInterface
{
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct(DispatcherFactory $dispatcherFactory)
    {
        $this->dispatcher = $dispatcherFactory->getDispathcer('http');
    }

    public function dispatch(ServerRequestInterface $request): ServerRequestInterface
    {
        $httpMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        $dispatched = new Dispatched($routeInfo);
        return Context::set(ServerRequestInterface::class,$request->withAttribute(Dispatched::class, $dispatched));
    }

}