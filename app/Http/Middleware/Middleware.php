<?php
// +----------------------------------------------------------------------
// | SlimPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: Karnc
// +----------------------------------------------------------------------

namespace App\Http\Middleware;

class Middleware
{
    protected  $container;

    public function __construct($container)
    {
        $this->container=$container;
    }

}
?>