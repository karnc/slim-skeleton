<?php

// +----------------------------------------------------------------------
// | SlimPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: Karnc
// +----------------------------------------------------------------------

namespace App\Http\Controllers;

class Controller
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }
}