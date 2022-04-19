<?php

namespace Src\HttpServer\Router;

use FastRoute\Dispatcher;
use Hyperf\HttpServer\Router\Handler;

class Dispatched
{
    /**
     * @var int
     */
    public $status;

    /**
     * @var null|Handler
     */
    public $handler;

    /**
     * @var array
     */
    public $params = [];

    /**
     * 针对提供的 HTTP 方法动词和 URI 进行调度。
     *
     * @param array $array with one of the following formats:
     *
     *     [Dispatcher::NOT_FOUND]
     *     [Dispatcher::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
     *     [Dispatcher::FOUND, $handler, ['varName' => 'value', ...]]
     */
    public function __construct(array $array)
    {
        $this->status = $array[0];
        switch ($this->status) {
            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->params = $array[1];
                break;
            case Dispatcher::FOUND:
                $this->handler = $array[1];
                $this->params = $array[2];
                break;
        }
    }

    public function isFound(): bool
    {
        return $this->status === Dispatcher::FOUND;
    }
}