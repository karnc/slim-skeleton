<?php
// +----------------------------------------------------------------------
// | SLIMPHP [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Author: karnc <75611897@qq.com>
// +----------------------------------------------------------------------

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string $make
     * @return mixed|\Interop\Container\ContainerInterface
     */
    function app($make = null)
    {
        global $app;

        if (is_null($make)) {
            return $app->getContainer();
        }

        return $app->getContainer()->get($make);
    }
}

if (!function_exists('input')) {
    /**
     * 获取输入数据 支持默认值和过滤
     * 使用方法:
     * <code>
     * input('id',0); 获取id参数 自动判断get或者post
     * input('post.name','','htmlspecialchars'); 获取$_POST['name']
     * input('get.'); 获取$_GET
     * </code>
     * @param string $name 变量的名称 支持指定类型
     * @param mixed $default 不存在的时候默认值
     * @param mixed $filter 参数过滤方法
     * @param mixed $datas 要获取的额外数据源
     * @return mixed
     */
    function input($name, $default = '', $filter = null, $datas = null)
    {
        if (strpos($name, '/')) { // 指定修饰符
            list($name, $type) = explode('/', $name, 2);
        } else { // 默认强制转换为字符串
            $type = 's';
        }
        if (strpos($name, '.')) { // 指定参数来源
            list($method, $name) = explode('.', $name, 2);
        } else { // 默认为自动判断
            $method = 'param';
        }
        switch (strtolower($method)) {
            case 'get'     :
                $input =& $_GET;
                break;
            case 'post'    :
                $input =& $_POST;
                break;
            case 'put'     :
                parse_str(file_get_contents('php://input'), $input);
                break;
            case 'param'   :
                switch ($_SERVER['REQUEST_METHOD']) {
                    case 'POST':
                        $input = $_POST;
                        break;
                    case 'PUT':
                        parse_str(file_get_contents('php://input'), $input);
                        break;
                    default:
                        $input = $_GET;
                }
                break;
            case 'request' :
                $input =& $_REQUEST;
                break;
            case 'session' :
                $input =& $_SESSION;
                break;
            case 'cookie'  :
                $input =& $_COOKIE;
                break;
            case 'server'  :
                $input =& $_SERVER;
                break;
            case 'globals' :
                $input =& $GLOBALS;
                break;
            case 'data'    :
                $input =& $datas;
                break;
            default:
                return NULL;
        }
        if ('' == $name) { // 获取全部变量
            $data = $input;
            $filters = isset($filter) ? $filter : 'htmlspecialchars';
            if ($filters) {
                if (is_string($filters)) {
                    $filters = explode(',', $filters);
                }
                foreach ($filters as $filter) {
                    $data = array_map_recursive($filter, $data); // 参数过滤
                }
            }
        } elseif (isset($input[$name])) { // 取值操作
            $data = $input[$name];
            $filters = isset($filter) ? $filter : 'htmlspecialchars';
            if ($filters) {
                if (is_string($filters)) {
                    $filters = explode(',', $filters);
                } elseif (is_int($filters)) {
                    $filters = array($filters);
                }

                foreach ($filters as $filter) {
                    if (function_exists($filter)) {
                        $data = is_array($data) ? array_map_recursive($filter, $data) : $filter($data); // 参数过滤
                    } elseif (0 === strpos($filter, '/')) {
                        // 支持正则验证
                        if (1 !== preg_match($filter, (string)$data)) {
                            return isset($default) ? $default : NULL;
                        }
                    } else {
                        $data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                        if (false === $data) {
                            return isset($default) ? $default : NULL;
                        }
                    }
                }
            }
            if (!empty($type)) {
                switch (strtolower($type)) {
                    case 'a':    // 数组
                        $data = (array)$data;
                        break;
                    case 'd':    // 数字
                        $data = (int)$data;
                        break;
                    case 'f':    // 浮点
                        $data = (float)$data;
                        break;
                    case 'b':    // 布尔
                        $data = (boolean)$data;
                        break;
                    case 's':   // 字符串
                    default:
                        $data = (string)$data;
                }
            }
        } else { // 变量默认值
            $data = isset($default) ? $default : NULL;
        }
        is_array($data) && array_walk_recursive($data, 'slim_filter');
        return $data;
    }
}

function array_map_recursive($filter, $data)
{
    $result = array();
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val)
            ? array_map_recursive($filter, $val)
            : call_user_func($filter, $val);
    }
    return $result;
}

function slim_filter(&$value)
{
    // 过滤查询特殊字符
    if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
        $value .= ' ';
    }
}

function http_post($url, $param, $post_file = false, $headers = [])
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
 * 检查时间戳和签名是否有效
 * @param string
 * @param string
 * @return string
 */
function CheckTimex($time, $code) {
    $t = (int) $time;
    $now = strtotime(date("Y-m-d H:i:s"));
    if (abs($t - $now) > 200) //如果时间相差超过10分钟，请确保服务器时间是标准北京时间
        return false;
    $string = strtoupper(md5(Encryptionx($time)));
    if ($code != $string) //如果验证字符串不相等则失败
        return false;
    return true;
}


/**
 * 简易签名算法
 * @param string
 * @return string
 */
function Encryptionx($s) {
    if (strlen($s) > 8)
        $s = substr($s, strlen($s) - 8);
    if ($s < 100000000)
        $s += 100000000;
    $y = $s * 1.8061825125;
    $y += 4852095.48659959;
    $y += ord(substr($s, 3, 1)) * 943579.486548;
    $y += ord(substr($s, 7, 1)) + 4889894.30184;
    $s = (string) $y;
    $y -= 458735.48548;
    $y -= ord(substr($s, 6, 1)) * 458720.454545;
    $y -= ord(substr($s, 2, 1)) * 5447826.96889;
    $s = (string) $y;
    $y += 34779810.2455;
    $y -= ord(substr($s, 1, 1)) * 57942.0564845;
    $y -= ord(substr($s, 5, 1)) * 1548.487866;
    $y = (int) $y;
    return (string) $y;
}

