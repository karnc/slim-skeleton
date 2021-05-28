<?php

namespace App\Services;

class Pinduoduo
{

    const URL = 'http://gw-api.pinduoduo.com/api/router';

    static $AUTHORIZE_API_ARR = [
        'MERCHANT' => 'https://mms.pinduoduo.com/open.html?',
        'H5' => 'https://mai.pinduoduo.com/h5-login.html?',
        'JINBAO' => 'https://jinbao.pinduoduo.com/open.html?',
    ];

    private $client_id;
    private $client_secret;
    private $member_type;
    public $redirect_uri;

    public function __construct($options)
    {
        $this->client_id = isset($options['client_id']) ? $options['client_id'] : '';
        $this->client_secret = isset($options['client_secret']) ? $options['client_secret'] : '';
        $this->member_type = isset($options['member_type']) ? $options['member_type'] : '';
        $this->redirect_uri = isset($options['redirect_uri']) ? $options['redirect_uri'] : '';
    }

    /**
     * 重定向至授权 URL.
     *
     * @param      $state
     * @param null $view
     */
    public function authorizationRedirect($state = 'state', $view = null)
    {
        $url = $this->authorizationUrl($state, $view);

        header('Location:' . $url);
    }

    /**
     * 获取授权URL.
     *
     * @param string $state
     * @param string $view
     *
     * @return string
     */
    public function authorizationUrl($state = null, $view = null)
    {
        return self::$AUTHORIZE_API_ARR[strtoupper($this->member_type)] . http_build_query([
                'client_id' => $this->client_id,
                'response_type' => 'code',
                'state' => $state,
                'redirect_uri' => $this->redirect_uri,
                'view' => $view,
            ]);
    }

    /**
     * 签名算法
     *
     * @param $params
     *
     * @return string
     */
    private function signature($params)
    {
        ksort($params);
        $paramsStr = '';
        array_walk($params, function ($item, $key) use (&$paramsStr) {
            if ('@' != substr($item, 0, 1)) {
                $paramsStr .= sprintf('%s%s', $key, $item);
            }
        });

        return strtoupper(md5(sprintf('%s%s%s', $this->client_secret, $paramsStr, $this->client_secret)));
    }

    /**
     * @param string $method
     * @param array $params
     * @param string $data_type
     *
     * @return mixed
     */
    public function request($method, $params, $data_type = 'JSON')
    {
        $params = $this->paramsHandle($params);
        $params['client_id'] = $this->client_id;
        $params['sign_method'] = 'md5';
        $params['type'] = $method;
        $params['data_type'] = $data_type;
        $params['timestamp'] = strval(time());
        $params['sign'] = $this->signature($params);
        $response = $this->http_post(self::URL, $params);
        return strtolower($data_type) == 'json' ? json_decode($response, true) : $response;
    }

    /**
     * Get token from remote server.
     * @param string $code
     * @return mixed
     */
    public function getTokenFromServer($code)
    {
        return  $this->request('pdd.pop.auth.token.create',['code'=>$code]);
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @param $headers
     * @return string content
     */
    private function http_post($url, $param, $post_file = false, $headers = [])
    {
        $oCurl = curl_init();
        if (count($headers) >= 1) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
        }
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curlFile = true;
        } else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        if (is_string($param)) {
            $strPOST = $param;
        } elseif ($post_file) {
            if ($is_curlFile) {
                foreach ($param as $key => $val) {
                    if (substr($val, 0, 1) == '@') {
                        $param[$key] = new \CURLFile(realpath(substr($val, 1)));
                    }
                }
            }
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);


        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function paramsHandle(array $params)
    {
        array_walk($params, function (&$item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            if (is_bool($item)) {
                $item = ['false', 'true'][intval($item)];
            }
        });

        return $params;
    }
}