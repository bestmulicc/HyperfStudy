<?php

namespace Src\Exception;

use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class ExceptionHandler
{
    /**
     * 处理异常，并返回指定的结果。
     */
    abstract public function handle(Throwable $throwable, ResponseInterface $response);

    /*
     * 确定当前异常处理程序是否应该处理异常。
     *
     * @return bool
     *      如果返回true，则此异常处理程序将处理该异常，
     *      如果返回 false，则委托给下一个处理程序
     */
    abstract public function isValid(Throwable $throwable):bool;

    /*
     * 停止将异常传播到下一个处理程序。
     */
    public function stopPropagation():bool
    {
        Propagation::instance()->setPropagationStopped(true);
        return true;
    }

    /*
     * 传播停止了吗？
     *      通常仅由处理程序用于确定前处理程序是否停止传播。
     */
    public function isPropagationStopped():bool
    {
        return Propagation::instance()->isPropagationStopped();
    }
}