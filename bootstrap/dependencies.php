<?php
// +----------------------------------------------------------------------
// | SLIMPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: karnc <75611897@qq.com>
// +----------------------------------------------------------------------

use Respect\Validation\Validator as v;

// DIC configuration
$container = $app->getContainer();


/*
|--------------------------------------------------------------------------
| 初始化数据库并注入
|--------------------------------------------------------------------------
*/
$capsule = new  \Illuminate\Database\Capsule\Manager();
$db = $container->get('settings')['db'];
foreach ($db as $key => $value) {
    $capsule->addConnection($value, $key);
}
$capsule->setAsGlobal();
$capsule->bootEloquent();
$capsule->getConnection()->enableQueryLog();
$container['db'] = function ($c) use ($capsule) {
    return $capsule;
};

/*
|--------------------------------------------------------------------------
| Cache handler
|--------------------------------------------------------------------------
*/
$container['cache'] = function ($c){
    $cache = new \Doctrine\Common\Cache\FilesystemCache(
        $c->get('settings')['cache']['path'],
        $c->get('settings')['cache']['ext']
    );
    return $cache;
};


/*
|--------------------------------------------------------------------------
| 用户身份验证
|--------------------------------------------------------------------------
*/
$container['auth'] = function($c) {
    return new \App\Services\Auth;
};

/*
|--------------------------------------------------------------------------
| 日志记录方式注册
|--------------------------------------------------------------------------
*/
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

/*
|--------------------------------------------------------------------------
| Flash messages
|--------------------------------------------------------------------------
*/
/*$container['flash'] = function ($c) {
    return new \Slim\Flash\Messages;
};*/


/*
|--------------------------------------------------------------------------
| 注册视图模板
|--------------------------------------------------------------------------
*/
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig($c->get("settings")["templates"]["template_dir"],
        [
            'cache' => $c->get("settings")["templates"]["cache_dir"],
            'auto_reload' => true,
            'charset' => 'utf-8',
        ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $c->router,
        $c->request->getUri()
    ));

    /*$view->getEnvironment()->addGlobal('auth', [
        'check' => $c->auth->check(),
        'user' => $c->auth->user()
    ]);*/

    /*$view->getEnvironment()->addGlobal('flash', $c->flash);*/

    return $view;
};


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/
$container['validator'] = function ($container) {
    return new App\Http\Validation\Validator;
};
v::with('App\\Http\\Validation\\Rules\\');

