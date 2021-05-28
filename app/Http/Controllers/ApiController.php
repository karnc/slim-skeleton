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

class ApiController extends Controller
{

    /**
     * 拼多多如云自定义接口
     * @return void
     */
    public function router($request, $response)
    {
        $params = $request->getParsedBody();
        if (!isset($params['client_id']) || !isset($params['type']) || !isset($params['data_type'])) {
            return $response->withJson(['error_response' => '参数错误']);
        }
        return http_post('http://gw-api.pinduoduo.com/api/router', $params);
    }

    /**
     * 拼多多接口中转站
     * @return void
     */
    public function transfer($request, $response)
    {

        $error_response = array(
            'error_msg' => ''
        );

        $time = input('time', 0, 'intval');
        $code = input('code', '', 'trim');

        if (!CheckTimex($time, $code)) {
            $error_response['error_msg'] = 'Not Key';
            return $response->withJson(array('error_response' => $error_response));
        }

        $params = input('params', '', 'trim');
        $params = json_decode($params, true);

        if (!isset($params['method'])) {
            $error_response['error_code'] = 10000;
            $error_response['error_msg'] = '参数错误';
            return $response->withJson(array('error_response' => $error_response));
        }

        $method = $params['method'];
        unset($params['method']);

        $option = $this->settings['pinduoduoServe'];
        $pinduoduo = new Pinduoduo($option);

        $res = $pinduoduo->request($method, $params);
        return $response->withJson($res);
    }


    /**
     * 拼多多接口中转站(走dts)
     * @return void
     */
    public function pddTransfer($request, $response)
    {

        $error_response = array(
            'error_msg' => ''
        );

        $post = input('params', '', 'trim');
        if (empty($post)) {
            $error_response['error_msg'] = '参数错误';
            return $response->withJson(array('error_response' => $error_response));
        }

        $post = json_decode($post, true);
        if (empty($post)) {
            $error_response['error_msg'] = '参数错误';
            return $response->withJson(array('error_response' => $error_response));
        }

        if (!isset($post['timestamp']) || !isset($post['encCode'])) {
            $error_response['error_msg'] = '参数错误';
            return $response->withJson(array('error_response' => $error_response));
        }

        $time = $post['timestamp'];
        $code = $post['encCode'];

        if (!CheckTimex($time, $code)) {
            $error_response['error_msg'] = 'Not Key';
            return $response->withJson(array('error_response' => $error_response));
        }

        if (!isset($post['type'])) {
            $error_response['error_msg'] = '参数错误';
            return $response->withJson(array('error_response' => $error_response));
        }

        $type = $post['type'];

        if ($type == 'pdd.order.list.get') {

            $page = $post['page'];
            $page_size = $post['page_size'];

            $order_status = $post['order_status'];
            $refund_status = $post['refund_status'];

            $start_confirm_at = date('Y-m-d H:i:s', $post['start_confirm_at']);
            $end_confirm_at = date('Y-m-d H:i:s', $post['end_confirm_at']);

            $use_has_next = $post['use_has_next'];
            $mall_id = $post['mall_id'];

            $builder = Trade::query();

            $builder->where('mall_id', '=', $mall_id);

            if ($order_status) {
                if ($order_status != 5) {
                    $builder->where('order_status', '=', $order_status);
                }
            }

            if ($refund_status) {
                if ($refund_status != 5) {
                    $builder->where('refund_status', '=', $refund_status);
                }
            }

            if ($start_confirm_at && $end_confirm_at) {
                $where['pdp_created'] = array(array('gt', $start_confirm_at), array('lt', $end_confirm_at));

                $builder->where(function ($query) use ($start_confirm_at, $end_confirm_at) {
                    $query->where('pdp_created', '>', $start_confirm_at)->where('pdp_created', '<', $end_confirm_at);
                });
            } else {
                $error_response['error_msg'] = '参数错误';
                return $response->withJson(array('error_response' => $error_response));
            }

            $count = $builder->count();
            $lists = collect($builder->offset(($page - 1) * $page_size)->limit($page_size)->orderBy('id', 'desc')->get())->toArray();
            $now_max_count = $page_size * $page;

            if (empty($lists)) {
                $result = array();
                $result['order_list_get_response'] = array('has_next' => false, 'order_list' => array(), 'total_count' => 0);
                return $response->withJson($result);
            } else {
                $result = array();
                $data = array();
                foreach ($lists as $k => $v) {
                    $order = $v['pdp_response'];
                    $order = json_decode($order, true);
                    $data[] = $order;
                }
                $order_list_get_response = array();
                $order_list_get_response['order_list'] = $data;
                $order_list_get_response['total_count'] = count($lists);
                if ($use_has_next) {
                    if ($now_max_count <= $count) {
                        $has_next = true;
                    } else {
                        $has_next = false;
                    }
                    $order_list_get_response['has_next'] = $has_next;
                }

                $result['order_list_get_response'] = $order_list_get_response;
                return $response->withJson($result);
            }
        } else if ($type == 'pdd.order.information.get') {
            $order_sn = $post['order_sn'];
            $info = Trade::query()->where('order_sn', '=', $order_sn)->first();
            if (empty($info)) {
                $error_response['error_msg'] = '订单信息不存在';
                return $response->withJson(array('error_response' => $error_response));
            } else {
                $order = $info['pdp_response'];
                $order = json_decode($order, true);
                $result = array();
                $result['order_info_get_response'] = array('order_info' => $order);
                return $response->withJson($result);
            }

        } else {
            unset($post['data_type']);
            unset($post['timestamp']);
            unset($post['client_id']);
            unset($post['type']);
            unset($post['sign']);

            $option = $this->settings['pinduoduoServe'];
            $pinduoduo = new Pinduoduo($option);

            $res = $pinduoduo->request($type, $post);
            return $response->withJson($res);
        }
    }

    /**
     * 拼多多接口中转站(不走dts)
     * @return void
     */
    public function pddTransferNoDatabase($request, $response)
    {

        $error_response = array(
            'error_msg' => ''
        );

        $post = input('params', '', 'trim');
        if (empty($post)) {
            $error_response['error_msg'] = '参数错误';
            return $response->withJson(array('error_response' => $error_response));
        }

        $post = json_decode($post, true);
        if (empty($post)) {
            $error_response['error_msg'] = '参数错误';
            return $response->withJson(array('error_response' => $error_response));
        }

        if (!isset($post['timestamp']) || !isset($post['encCode'])) {
            $error_response['error_msg'] = '参数错误';
            return $response->withJson(array('error_response' => $error_response));
        }

        $time = $post['timestamp'];
        $code = $post['encCode'];

        if (!CheckTimex($time, $code)) {
            $error_response['error_msg'] = 'Not Key';
            return $response->withJson(array('error_response' => $error_response));
        }

        if (!isset($post['type'])) {
            $error_response['error_msg'] = '参数错误';
            return $response->withJson(array('error_response' => $error_response));
        }

        $type = $post['type'];

        unset($post['data_type']);
        unset($post['timestamp']);
        unset($post['client_id']);
        unset($post['type']);
        unset($post['sign']);

        $option = $this->settings['pinduoduoServe'];
        $pinduoduo = new Pinduoduo($option);

        $res = $pinduoduo->request($type, $post);
        return $response->withJson($res);
    }

}
