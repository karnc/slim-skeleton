<?php
// +----------------------------------------------------------------------
// | SlimPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: Karnc
// +----------------------------------------------------------------------

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Respect\Validation\Validator as v;
use Firebase\JWT\JWT;
use Tuupola\Base62;
use App\Models\User;

class AuthController extends Controller
{
    public function getSignOut($request, $response)
    {
        $this->auth->logout();
        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getSignIn($request, $response)
    {
        return $this->view->render($response, 'auth/signin.twig');
    }

    public function postSignIn($request, $response)
    {
        $auth = $this->auth->attempt(
            $request->getParam('email'),
            $request->getParam('password')
        );

        if (!$auth) {
            $this->flash->addMessage('error', '无法用这些详细信息让您登录');
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getSignUp($request, $response)
    {
        return $this->view->render($response, 'auth/signup.twig');
    }

    public function postSignUp($request, $response)
    {

        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
            'name' => v::noWhitespace()->notEmpty()->alpha(),
            'password' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }

        $user = User::create([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name'),
            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
        ]);

        $this->flash->addMessage('info', '你已经注册了');

        $this->auth->attempt($user->email, $request->getParam('password'));

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function auth($request, $response)
    {
        $settings = $this->container->get('settings')['jwtAuthentication'];

        $requested_scopes = $request->getParsedBody();

        $mobile = isset($requested_scopes['mobile']) ? $requested_scopes['mobile'] : '';
        $password = isset($requested_scopes['password']) ? $requested_scopes['password'] : '';

        //  START 验证用户账号信息 : 此处需要替换为查询操作
        $userId = 1;
        $mobile = '18256054403';
        $scopes = [
            'userId' => $userId,
            'mobile' => $mobile
        ];
        // END

        if (empty($scopes)) {
            return $response->withStatus(401)
                ->withHeader("Content-Type", "application/json")
                ->withJson(['code' => 401, 'message' => 'invalid credentials']);
        }

        $now = new \DateTime();
        $future = new \DateTime("now +{$settings['expires']}");
        $jti = (new Base62)->encode(random_bytes(16));

        $payload = [
            "iat" => $now->getTimeStamp(), // Issued At - When the token was issued (unix timestamp)
            "exp" => $future->getTimeStamp(), // Expiry - The token expiry date (unix timestamp)
            "jti" => $jti, // JWT Id - A unique identifier for the token (md5 of the sub and iat claims)
            "sub" => $userId, // Subject - This holds the identifier for the token (defaults to user id)
            "scope" => $scopes
        ];

        $secret = $settings['secret'];
        $token = JWT::encode($payload, $secret, "HS256");

        $data["token"] = $token;
        $data["expires"] = $future->getTimeStamp();

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json")
            ->withJson($data);

    }

    public function dump($request, $response)
    {
        var_dump($_SESSION);
        var_dump($this->token->getScope());
        exit;
    }
}