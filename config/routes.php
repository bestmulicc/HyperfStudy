<?php

return [
    //['GET','/hello','HelloController@index'],
    ['GET','/hello/index',[\App\Controller\HelloController::class,'index']],
    ['GET','/hello/hyperf',[\App\Controller\HelloController::class,'hyperf']],
];