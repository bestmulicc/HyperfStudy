<?php

namespace App\Exception\Handler;

use App\Exception\FooException;
use App\Exception\ZiException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Src\Exception\ExceptionHandler;
use Throwable;

class ZiExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 阻止异常冒泡
        $this->stopPropagation();
        // 交给下一个异常处理器
        return $response->withStatus(510)->withBody(new SwooleStream('This is the ZiExceptionHandler!'));
    }

    public function isValid(Throwable $throwable):bool
    {
        var_dump('Ziiiiiiiiii');
        return $throwable instanceof ZiException;
    }
}