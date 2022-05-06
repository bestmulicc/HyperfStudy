<?php

namespace App\Controller;

use App\Exception\FooException;
class ExController
{
    public function exception()
    {
        throw new FooException('FooException...',800);
    }
}