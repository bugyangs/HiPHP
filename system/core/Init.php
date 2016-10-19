<?php

class System_Core_Init {
    private static $instance = null;
    /**
     * @param $appName
     * @return null|System_Core_Init
     */
    public static function instance($appName) {
        if(!self::$instance) {
            self::$instance = new System_Core_Init($appName);
        }
        return self::$instance;
    }

    /**
     * @param $appName
     */
    private function __construct($appName) {
        $this->init($appName);
    }

    /**
     * @param $appName
     */
    private function init($appName) {
        // 初始化基础环境
        $this->initBasicEnv();

        // 初始化App环境
        $this->initAppEnv($appName);

        $this->initAutoload();
    }

    /**
     * @return bool
     */
    private function initBasicEnv() {
        header("Content-type:text/html;charset=utf-8");
        date_default_timezone_set('PRC');
        // 页面启动时间(us)，PHP5.4可用$_SERVER['REQUEST_TIME']
        define('REQUEST_TIME_US', intval(microtime(true)*1000000));
        // site预定义路径
        define('ROOT_PATH', realpath(dirname(__FILE__) . '/../../'));
        define('LOG_PATH', ROOT_PATH.'/log');
        define('SYSTEM_PATH', ROOT_PATH.'/system');
        return true;
    }

    /**
     * @param $app_name
     * @return bool
     */
    private function initAppEnv($app_name) {
        // 检测当前App
        if($app_name != null || ($app_name = $this->getAppName()) != null) {
            define('APP_NAME', $app_name);
        }
        else {
            define('APP_NAME', 'unknown-app');
        }
        define('APP_PATH', ROOT_PATH . '/app/' . APP_NAME);

        return true;
    }

    /**
     * @throws Exception
     */
    private function initAutoload() {
        include_once SYSTEM_PATH . "/core/Loader.php";
        System_Core_Loader::registerAutoload();
    }

    /**
     * @return string
     */
    private function getAppName() {
        $app_name = "";
        if(PHP_SAPI != 'cli') {

        }
        else
        {
            $file = $_SERVER['argv'][0];
            if($file{0} != '/')
            {
                $cwd = getcwd();
                $full_path = realpath($file);
            }
            else
            {
                $full_path = $file;
            }

            if(strpos($full_path, APP_PATH.'/') === 0)
            {
                $s = substr($full_path, strlen(APP_PATH)+1);
                if(($pos = strpos($s, '/')) > 0)
                {
                    $app_name = substr($s, 0, $pos);
                }
            }
        }
        return $app_name;
    }
}