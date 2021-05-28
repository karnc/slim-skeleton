<?php
// +----------------------------------------------------------------------
// | SlimPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: Karnc
// +----------------------------------------------------------------------

$app->get('/', 'App\Http\Controllers\PageController:index')->setName('home');

$app->group('/index', function () {
    $this->any('/api/transfer', 'App\Http\Controllers\ApiController:transfer')->setName('index.api.transfer');
    $this->any('/api/pddTransfer', 'App\Http\Controllers\ApiController:pddTransfer')->setName('index.api.pddTransfer');
});