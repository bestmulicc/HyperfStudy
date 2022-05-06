<?php

namespace App\Exception\Handler;

use App\Exception\FooException;
use Src\Exception\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;


class FooExceptionHandler extends ExceptionHandler
{

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 阻止异常冒泡
        $this->stopPropagation();
        // 交给下一个异常处理器
        return $response->withStatus(510)->withBody(new SwooleStream('This is the FooExceptionHandler!'));
    }

    public function isValid(Throwable $throwable):bool
    {
        return $throwable instanceof FooException;
    }
}