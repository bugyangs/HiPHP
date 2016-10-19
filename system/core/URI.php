<?php

/**
 * Class System_Core_URI
 */
class System_Core_URI {

    private static $_instance = null;

    /**
     * 构造函数
     */
    private function __construct() {

    }

    /**
     * @return null|System_Core_URI
     */
    public static function getInstance() {
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 返回请求的uri
     * @return mixed|string
     */
    public function detectUri() {
        if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME'])) {
            return '';
        }
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        }
        elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }
        $parts = preg_split('#\?#i', $uri, 2);
        $uri = $parts[0];
        if (isset($parts[1])) {
            $_SERVER['QUERY_STRING'] = $parts[1];
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }
        else {
            $_SERVER['QUERY_STRING'] = '';
            $_GET = array();
        }
        if ($uri == '/' || empty($uri)) {
            return '';
        }
        $uri = parse_url($uri, PHP_URL_PATH);
        $url = str_replace(array('//', '../'), '/', trim($uri, '/'));
        return $this->removeInvisibleCharacters($url);
    }

    /**
     * 移除连接特殊字符
     * @param $str
     * @param bool|TRUE $url_encoded
     * @return mixed
     */
    public function removeInvisibleCharacters($str, $url_encoded = TRUE)
    {
        $non_displayables = array();

        // every control character except newline (dec 10)
        // carriage return (dec 13), and horizontal tab (dec 09)

        if ($url_encoded)
        {
            $non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

        do
        {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        }
        while ($count);

        // Convert programatic characters to entities
        $bad	= array('$',		'(',		')',		'%28',		'%29');
        $good	= array('&#36;',	'&#40;',	'&#41;',	'&#40;',	'&#41;');

        return str_replace($bad, $good, $str);
//        return $str;
    }

    /**
     * @param $str
     * @return mixed
     */
    function filterUri($str)
    {
        // Convert programatic characters to entities
        $bad	= array('$',		'(',		')',		'%28',		'%29');
        $good	= array('&#36;',	'&#40;',	'&#41;',	'&#40;',	'&#41;');
        return str_replace($bad, $good, $str);
    }

}