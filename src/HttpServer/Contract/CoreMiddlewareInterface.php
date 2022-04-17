<?php

namespace Src\HttpServer\Contract;

use MongoDB\BSON\MinKeyInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface CoreMiddlewareInterface extends MiddlewareInterface
{
    public function dispatch(ServerRequestInterface $request):ServerRequestInterface;
}