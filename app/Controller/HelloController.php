<?php

namespace App\Controller;

class HelloController
{
//      1,从routes中获取
//    public function index()
//    {
//        return 'Hello Hyperf';
//    }

//      2，从path中获取
//    /**
//     * @path /hello/index
//     * @return string
//     */
//    public function index()
//    {
//        return 'Hello Hyperf';
//    }
//
//    /**
//     * @path /hello/hyperf
//     * @return string
//     */
//    public function hyperf()
//    {
//        return 'Hyperf Hello';
//    }
    public function index()
    {
        return 'Hello Hyperf';
    }
    public function hyperf()
    {
        return 'Hyperf Hello';
    }
}