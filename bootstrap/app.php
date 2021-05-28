<?php
// +----------------------------------------------------------------------
// | SLIMPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: karnc <75611897@qq.com>
// +----------------------------------------------------------------------

require __DIR__ . '/../vendor/autoload.php';

try {
    \Dotenv\Dotenv::createMutable(__DIR__ . '/../')->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die('config load failed!');
}

$settings = require __DIR__ . '/settings.php';

/*
|--------------------------------------------------------------------------
| Tracy
|--------------------------------------------------------------------------
*/
use Tracy\Debugger;
Debugger::enable(Debugger::DEVELOPMENT);
Debugger::$showBar =$settings['settings']['displayErrorDetails'];

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/
$app = new \Slim\App($settings);

/*
|--------------------------------------------------------------------------
| Set up Dependencies
|--------------------------------------------------------------------------
*/
require __DIR__ . '/dependencies.php';

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
*/
require __DIR__ . '/middleware.php';

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
*/
$routes = glob(__DIR__ . '/../routes/*.php');
foreach ($routes as $route) {
    require $route;
}

return $app;