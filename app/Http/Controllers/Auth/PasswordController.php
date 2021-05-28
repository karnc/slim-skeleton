<?php
// +----------------------------------------------------------------------
// | SlimPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: Karnc
// +----------------------------------------------------------------------

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Respect\Validation\Validator as v;

class PasswordController extends Controller
{
    public function getChangePassword($request, $response)
    {
        return $this->view->render($response, 'auth/password/change.twig');
    }

    public function postChangePassword($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'password_old' => v::noWhitespace()->notEmpty()->matchesPassword($this->auth->user()->password)->setName('密码'),
            'password' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.password.change'));
        }

        $this->auth->user()->setPassword($request->getParam('password'));

        $this->flash->addMessage('info', 'Your password was changed');

        return $response->withRedirect($this->router->pathFor('home'));

    }
}