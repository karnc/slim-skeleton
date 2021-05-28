<?php
// +----------------------------------------------------------------------
// | SLIMPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: karnc <75611897@qq.com>
// +----------------------------------------------------------------------


// DIC configuration
$container = $app->getContainer();


/*
|--------------------------------------------------------------------------
|  部分中间件注册
|--------------------------------------------------------------------------
*/
#$app->add(new \App\Http\Middleware\ValidationErrorsMiddleware($container));
#$app->add(new \App\Http\Middleware\OldInputMiddleware($container));
#$app->add(new \App\Http\Middleware\CsrfViewMiddleware($container));

/*
|--------------------------------------------------------------------------
| JWT Token hydrate
|--------------------------------------------------------------------------
*/
$container["token"] = function ($c) {
    return new \App\Services\Token();
};
/*
|--------------------------------------------------------------------------
| JWT Authentication Middleware
|--------------------------------------------------------------------------
*/
$container["JwtAuthentication"] = function ($c) {

    $settings = $c->get('settings')['jwtAuthentication'];

    return new \Slim\Middleware\JwtAuthentication([
        "secure" => $settings['secure'],
        "relaxed" => $settings['relaxed'],
        "path" 	=> $settings['path'],
        "passthrough" => $settings['passthrough'],
        "secret" => $settings['secret'],
        "cookie" => $settings['cookie'],
        "logger" => $c["logger"],
        "error" => function ($request, $response, $arguments) {
            return $response->withStatus(401)
                ->withHeader("Content-type", "application/problem+json")
                ->withJson(['code' => 401, 'message' => $arguments["message"]]);
        },
        "callback" => function ($request, $response, $arguments) use ($c) {
            $c["token"]->hydrate($arguments["decoded"]);
        }
    ]);
};
$app->add("JwtAuthentication");


/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/
$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};
#$app->add($container->csrf);