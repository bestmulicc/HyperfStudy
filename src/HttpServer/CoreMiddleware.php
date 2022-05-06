<?php

namespace Src\HttpServer;

/*
 * Hyperf的核心中间件，主要负责处理路由信息
 * 然后委托给指定的handler（也就是Controller）来处理请求，
 * 生成一个响应对象并委托给下一个中间件（因为这个中间件是核心中间件，那么下一个中间件也意味着它是前一个中间件对象）。
 */
use FastRoute\Dispatcher;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use \Src\HttpServer\Router\Dispatched;
use \Src\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;
use Src\HttpServer\Contract\CoreMiddlewareInterface;
use Swoole\StringObject;

class CoreMiddleware implements CoreMiddlewareInterface
{
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct(DispatcherFactory $dispatcherFactory)
    {
        $this->dispatcher = $dispatcherFactory->getDispatcher('http');
    }

    public function dispatch(ServerRequestInterface $request): ServerRequestInterface
    {
        $httpMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        //存储了路由调度出来的结果的对象，方便中间件在不同的环节去修改路由匹配的一些规则
        $dispatched = new Dispatched($routeInfo);

        $request = Context::set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, $dispatched));
        return $request;
    }

    //处理传入的服务器请求并返回响应，可选择委派
    //对处理程序的响应创建。
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //获取request中的dispatched对象
        $dispatched = $request->getAttribute(Dispatched::class);
        if (! $dispatched instanceof Dispatched) {
            throw new \InvalidArgumentException('Route not found');
        }
        switch ($dispatched->status) {
            case Dispatcher::NOT_FOUND:
                $response = $this->handleNotFound($request);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = $this->handleMethodNotAllow($request);
                break;
            case Dispatcher::FOUND:
                $response = $this->handleFound($request, $dispatched);
                break;
        }
        if (! $response instanceof ResponseInterface) {
            $response = $this->transferToResponse($response);
        }
        return $response;
    }

    protected function handleNotFound(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ResponseInterface $response */
        return $response->withStatus(404)->withBody(new SwooleStream('Not found'));
    }

    protected function handleMethodNotAllow(ServerRequestInterface $request)
    {
        /** @var ResponseInterface $response */
        return $response->withStatus(405)->withBody(new SwooleStream('Method not allow'));
    }

    protected function handleFound(ServerRequestInterface $request, Dispatched $dispatched)
    {
        [$controller, $action] = $dispatched->handler;
        if (! class_exists($controller)) {
            throw new \InvalidArgumentException('Controller not exist');
        }
        if (! method_exists($controller, $action)) {
            throw new \InvalidArgumentException('Action of Controller not exist');
        }
        $parameters = [];
        $controllerInstance = new $controller();
        return $controllerInstance->{$action}(...$parameters);
    }

    protected function transferToResponse($response): ResponseInterface
    {
        if (is_string($response)) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'text/plain')
                ->withBody(new SwooleStream((string) $response));
        } elseif (is_array($response) || $response instanceof Arrayable) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream(Json::encode($response)));
        } elseif ($response instanceof Jsonable) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream((string) $response));
        }
        return $response;
    }

    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }
}