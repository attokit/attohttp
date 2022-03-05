<?php

namespace Atto\AttoHttp;

use Atto\AttoHttp\request\Url;

use Atto\AttoHttp\traits\staticCurrent;

class Request
{
    /**
     * use traits
     */
    use staticCurrent;
    
    /**
     * current request
     */
    public static $current = null;

    /**
     * request params
     */
    public $headers = [];
    public $method = "";
    public $https = false;
    public $isAjax = false;
    public $lang = "zh-CN";
    public $pause = false;
    public $debug = false;
    public $gets = [];
    public $posts = [];
    public $inputs = [];

    /**
     * construct
     */
    public function __construct()
    {
        $this->url = Url::current();
        $this->headers = self::getHeaders();
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->time = $_SERVER["REQUEST_TIME"];
        $this->https = $this->url->protocol == "https";
        $this->isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        //lang
        $this->lang = self::get("lang", defined("EXPORT_LANG") ? EXPORT_LANG : "zh-CN");
        //通过设置 WEB_PAUSE 暂停网站（src资源仍可以访问）
        $this->pause = defined("WEB_PAUSE") ? WEB_PAUSE : false;
        //debug标记
        $this->debug = defined("WEB_DEBUG") ? WEB_DEBUG : false;
    }


    /**
     * static
     */

    /**
     * get current Request object
     * import by traits
     * @return Request
     * 
     * public static function current()
     */

    /**
     * get request headers
     * @return Array
     */
    public static function getHeaders()
    {
        $hds = [];
        if (function_exists("getallheaders")) {     //Apache环境下
            $hds = getallheaders();
        } else {
            foreach ($_SERVER as $k => $v) {
                if (substr($k, 0, 5) == "HTTP_") {
                    $hds[str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($k, 5)))))] = $v;
                }
            }
        }
        return $hds;
    }

    /**
     * read and return $_GET[$key] with default value $val
     * @param Mixed     $key        String or Array, query key(s)
     * @param Mixed     $val        if $_GET[$key] not found use $val as default value
     * @return Mixed    filted query value
     */
    public static function get($key = [], $val = null)
    {
        if (is_array($key)) {
            if (empty($key)) return $_GET;
            $p = array();
            foreach ($key as $k => $v) {
                $p[$k] = self::get($k, $v);
            }
            return $p;
        }else{
            return isset($_GET[$key]) ? $_GET[$key] : $val;
        }
    }

    /**
     * read and return $_POST[$key] with default value $val
     * @param Mixed     $key        String or Array, query key(s)
     * @param Mixed     $val        if $_POST[$key] not found use $val as default value
     * @return Mixed    filted post value
     */
    public static function post($key = [], $val = null)
    {
        if (is_array($key)) {
            if (empty($key)) return $_POST;
            $p = array();
            foreach ($key as $k => $v) {
                $p[$k] = self::post($k,$v);
            }
            return $p;
        }else{
            return isset($_POST[$key]) ? $_POST[$key] : $val;
        }
    }

    /**
     * read and return php://input raw data usually in json format
     * @param String    $in         set input format, default json
     * @return Array    filted input data
     */
    public static function input($in = "json")
    {
        $input = file_get_contents("php://input");
        if (empty($input)) {
            $input = session_get("_php_input_", null);
            if (is_null($input)) return null;
            session_del("_php_input_");
        }
        $output = null;
        switch($in){
            case "json" :
                $output = j2a($input);
                break;
            case "xml" :
                $output = x2a($input);
                break;
            case "url" :
                $output = u2a($input);
                break;
            default :
                $output = arr($input);
                break;
        }
        return $output;
    }
}