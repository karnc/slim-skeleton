<?php

// +----------------------------------------------------------------------
// | SlimPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: Karnc
// +----------------------------------------------------------------------

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Services\Pinduoduo;
use Illuminate\Database\Capsule\Manager as DB;

class PageController extends Controller
{
    public function index($request, $response)
    {
        $app_name = $this->settings['app_name'];
        $app_version = $this->settings['app_version'];
        echo $app_name."：当前程序版本为".$app_version;exit;
    }
}
