<?php
// +----------------------------------------------------------------------
// | SLIMPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: karnc <75611897@qq.com>
// +----------------------------------------------------------------------

return [
    'settings' =>
        [
            // Slim Settings
            'displayErrorDetails' => env('APP_DEBUG'),
            'addContentLengthHeader' => false, // Allow the web server to send the content-length header

            // monolog settings
            'logger' => [
                'name' => 'slim',
                'path' => ROOT_PATH . 'runtime/logs' . DS . date("Y-m-d") . '-app.log',
                'level' => \Monolog\Logger::INFO,
            ],

            // Cache settings
            'cache' => [
                'path' => ROOT_PATH . 'runtime/cache',
                'ext' => '.cache',
            ],

            // Db settings
            'db' => [
                "default" =>
                    [
                        'driver' => 'mysql',
                        'host' => '127.0.0.1',
                        'port' => '3306',
                        'database' => 'test',
                        'username' => 'test',
                        'password' => 'test',
                        'charset' => 'utf8',
                        'collation' => 'utf8_general_ci',
                        'prefix' => '',
                    ]
            ],

            // View settings
            'templates' => [
                'cache_dir' => ROOT_PATH . 'runtime/temp',
                'template_dir' => ROOT_PATH . 'resources/views',
            ],

            //Jwt Authentication
            "jwtAuthentication" => [
                "secure" => false, // 是否开启安全模式: 验证 https和 IP
                "relaxed" => [], // IP白名单
                "cookie" => "token", // 用于鉴权的 token的 cookie 名称
                "secret" => 'eba7aa43d165fc6bf49c0549a8a55d35', // jwt 秘钥
                "path" => ["/api"], // 需要鉴权的路径
                "passthrough" => ["/api/router"], // 无需鉴权的路径
                "expires" => "24 hours", // 凭证有效期
            ],

            //电商平台设置
            'pinduoduoServe' => [
                'client_id' => '',
                'client_secret' => '',
                'member_type' => 'MERCHANT',
                'redirect_uri' => '',
            ],
            //网站设置
            'app_name'=>env('APP_NAME','Slim'),
            'app_version'=>env('APP_VERSION','1.0.0'),
        ],
];
