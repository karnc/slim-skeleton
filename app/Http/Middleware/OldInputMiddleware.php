<?php
// +----------------------------------------------------------------------
// | SlimPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: Karnc
// +----------------------------------------------------------------------

namespace App\Http\Middleware;

class OldInputMiddleware extends Middleware
{

	public function __invoke($request, $response, $next)
	{
		$this->container->view->getEnvironment()->addGlobal('old', isset($_SESSION['old']) ? $_SESSION['old'] : '');
		$_SESSION['old'] = $request->getParams();

		$response = $next($request, $response);
		return $response;
	}
}