<?php

namespace Src\HttpServer\Exception\Handler;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Src\Exception\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    /**
     * 处理异常，并返回指定的结果。
     * @param Throwable $throwable
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        return $response->withStatus(510)->withBody(new SwooleStream('This is the HttpExceptionHandler!'));
    }

    /**
     * 确定当前异常处理程序是否应该处理异常
     *      如果返回true，则此异常处理程序将处理该异常
     *      如果返回 false，则委托给下一个处理程序
     * @param Throwable $throwable
     * @return bool
     */
    public function isValid(Throwable $throwable):bool
    {
        return $throwable instanceof HttpException;
    }
}