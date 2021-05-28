<?php


$app->group('/api', function() use ($app) {
    // 获取登录凭证路由
    $this->post('/auth', 'App\Http\Controllers\Auth\AuthController:auth');
    // 测试鉴权路由
    $this->map(['POST', 'GET'], '/dump', 'App\Http\Controllers\Auth\AuthController:dump');
    //拼多多转发接口
    $this->any('/router', 'App\Http\Controllers\ApiController:router');
});