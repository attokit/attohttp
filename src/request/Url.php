<?php

namespace Atto\AttoHttp\request;

use Atto\AttoHttp\traits\staticCurrent;

class Url
{
    /**
     * use traits
     */
    use staticCurrent;
    
    /**
     * current url
     */
    public static $current = null;

    /**
     * URL params
     */
    public $full = "";
    public $protocol = "http";
    public $host = "";
    public $domain = "";
    public $uri = "";
    public $path = [];
    public $query = [];

    /**
     * construct
     * @param   String $url     Like url or path
     */
    public function __construct($url = "")
    {
        $this->protocol = self::protocol($url);
        $this->host = self::host($url);
        $this->domain = self::domain($url);
        $this->uri = self::uristr($url);
        $uri = self::uri($url);
        $this->path = $uri["path"];
        $this->query = $uri["query"];
        $this->full = $this->domain.$this->uri;
    }

    /**
     * combine current url with new url or path
     * @param   String $url     Like url or path
     * @return  Url
     */
    public function merge($url = "")
    {
        return self::mk($url, $this);
    }
    


    /**
     * static
     */

    /**
     * get protocol from url
     * @param   String $url     Like url or path
     * @return  String          http or https
     */
    public static function protocol($url = "")
    {
        if (!self::legal($url)) {
            $svr = $_SERVER;
            $protocol = $svr["SERVER_PROTOCOL"];
            if ($protocol == "HTTP/1.1") {
                if (!isset($svr["HTTPS"]) || $svr["HTTPS"] == "off" || empty($svr["HTTPS"])) {
                    return "http";
                }
            }
            return "https";
        } else {
            if (strpos(strtolower($url), "http://") !== false) return "http";
            if (strpos(strtolower($url), "https://") !== false) return "https";
            if (strpos($url, "://") !== false) {
                $pt = explode("://", $url);
                return $pt[0];
            }
            //return "http";
            return self::protocol();
        }
    }

    /**
     * get host from url
     * @param   String $url     Like url or path
     * @return  String          host name and port (if exists)
     */
    public static function host($url = "")
    {
        if (!self::legal($url)) {
            return $_SERVER["HTTP_HOST"];
        } else {
            $ua = explode("://", $url);
            if (count($ua) <= 1) return $_SERVER["HTTP_HOST"];
            $ua = explode("/", $ua[1]);
            return $ua[0];
        }
    }

    /**
     * get domain from url
     * @param   String $url     Like url or path
     * @return  String          = protocol() + :// + host()
     */
    public static function domain($url = "")
    {
        return self::protocol($url)."://".self::host($url);
    }

    /**
     * get REQUEST_URI from url
     * @param   String $url     Like url or path
     * @return  String          regular uri string begin with "/"
     */
    public static function uristr($url = "")
    {
        if (!self::legal($url)) return $_SERVER["REQUEST_URI"];
        return str_replace(self::domain($url), "", $url);
    }

    /**
     * parse URI from url
     * @param   String $url     Like url or path
     * @return  Array           [String uri, Array path,Array query, String pathstr, String querystr]
     */
    public static function uri($url = "")
    {
        $uristr = self::legal($url) || empty($url) ? self::uristr($url) : $url;
        $ua = strpos($uristr, "?") !== false ? explode("?", $uristr) : [ $uristr ];
        $ups = ltrim($ua[0], "/");
        $upath = !empty($ups) ? explode("/", $ups) : [];
        $qs = count($ua) < 2 ? "" : $ua[1];
        $q = empty($qs) ? [] : u2a($qs);
        return [
            "uri"       => $uristr,
            "path"      => $upath,
            "query"     => $q,
            "pathstr"   => $ua[0],
            "querystr"  => $qs
        ];
    }

    /**
     * get current url object
     * @param   String $url     Like url or path
     * @return  Url
     */
    public static function current()
    {
        $args = func_get_args();
        if (is_null(self::$current)) {
            self::$current = new Url(...$args);
        }
        return self::$current;
    }

    /**
     * check $url for legal URL String
     * @param   String $url     Like url or path
     * @return  Boolean
     */
    public static function legal($url = "")
    {
        if (is_string($url) && !empty($url)) {
            if (strpos($url, "://") !== false) return true;
        }
        return false;
    }

    /**
     * create Url object form url string
     * http://xxxx              return self
     * /xxxx/xxxx?qs            return protocol() + :// + host() + /xxxx/xxxx
     * ../../xxxx/xxxx?qs       return current() + /../../xxxx/xxxx
     * query combined as array
     * 
     * @param   String $url     Like url or path
     * @param   String $cu      Url object
     * @return  Url
     */
    public static function mk($url = "", $cu = null)
    {
        if (self::legal($url)) return new Url($url);
        $cu = empty($cu) || !($cu instanceof Url) ? self::current() : $cu;
        if (empty($url)) return $cu;
        $uri = self::uri($url);
        $uri["query"] = arr_extend($cu->query, $uri["query"]);
        $qs = empty($uri["query"]) ? "" : "?".a2u($uri["query"]);
        if (str_begin($url, "/")) {
            $nu = $cu->domain.$uri["pathstr"].$qs;
        } else {
            $uri["path"] = array_merge($cu->path, $uri["path"]);
            $nu = $cu->domain."/".path_up(implode("/", $uri["path"]), "/").$qs;
        }
        return new Url($nu);
    }

}