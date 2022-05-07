<?php

declare(strict_types=1);

use App\Exception\Handler\FooExceptionHandler;
use App\Exception\Handler\ZiExceptionHandler;

return [
    ZiExceptionHandler::class,
    FooExceptionHandler::class,
];