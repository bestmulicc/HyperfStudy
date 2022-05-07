<?php

namespace App\Controller;

use App\Exception\FooException;
use App\Exception\ZiException;

class ExController
{
    public function exceptionA()
    {
        throw new FooException('FooException...',800);
    }
    public function exceptionB()
    {
        throw new ZiException('ZiException...',800);
    }
}