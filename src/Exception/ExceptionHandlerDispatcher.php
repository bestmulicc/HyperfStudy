<?php

namespace Src\Exception;

use Src\Dispatcher\AbstractExceptionDispatcher;
use Hyperf\Context\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ExceptionHandlerDispatcher extends AbstractExceptionDispatcher
{
//    /**
//     * @var ContainerInterface
//     */
//    private $container;
//
//    public function __construct(ContainerInterface $container)
//    {
//        $this->container = $container;
//    }
    public function dispatch(...$params)
    {
        /**
         * @param Throwable $throwable
         * @param string[] $handler
         */
        [$throwable,$handlers] = $params;
        $response = Context::get(ResponseInterface::class);
        foreach ($handlers as $handler){
//            //判断异常处理器是否存在有效
//            if (! $this->container->has($handler)){
//                throw new \InvalidArgumentException(sprintf('Invalid exception handler %s',$handler));
//            }
            //从容器中取出处理器实体
            $handlerInstance = new $handler();
            if (! $handlerInstance instanceof ExceptionHandler || $handlerInstance->isValid($throwable)){
                continue;
            }
            $response = $handlerInstance->handle($throwable, $response);
            if ($handlerInstance->isPropagationStopped()){
                break;
            }
        }
        return $response;
    }
}