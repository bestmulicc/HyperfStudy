<?php

return [
    //['GET','/hello','HelloController@index'],
    ['GET','/hello/index',[\App\Controller\HelloController::class,'index'],[
        'middlewares' =>[
            \App\Middleware\MiddlewareB::class,
        ]
    ]],
    ['GET','/hello/hyperf',[\App\Controller\HelloController::class,'hyperf']],
    ['GET','/hello/num',[\App\Controller\HelloController::class,'num']],
    ['GET','/ex/A',[\App\Controller\ExController::class,'exceptionA']],
    ['GET','/ex/B',[\App\Controller\ExController::class,'exceptionB']]
];