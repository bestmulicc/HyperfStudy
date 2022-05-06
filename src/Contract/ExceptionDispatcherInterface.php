<?php

namespace Src\Contract;

interface ExceptionDispatcherInterface
{
    public function dispatch(...$params);
}