<?php

return [
    //['GET','/hello','HelloController@index'],
    ['GET','/hello/index',[\App\Controller\HelloController::class,'index'],[
        'middlewares' =>[
            \App\Middleware\MiddlewareB::class,
        ]
    ]],
    ['GET','/hello/hyperf',[\App\Controller\HelloController::class,'hyperf']],
];