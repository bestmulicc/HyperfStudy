<?php

namespace Src\HttpServer\Contract;

use MongoDB\BSON\MinKeyInterface;
use Psr\Http\Message\ServerRequestInterface;

interface CoreMiddlewareInterface
{
    public function dispatch(ServerRequestInterface $request):ServerRequestInterface;
}