<?php
/**
 * FileName: Loader.php
 * Author: zyf
 * Date: 16/9/19 下午4:17
 * Brief:
 */
class System_Core_Loader {
    public static $loaderMap = array();
    /**
     * @throws Exception
     */
    public static function registerAutoload() {
        if (!function_exists('spl_autoload_register')) {
            throw new Exception('spl_autoload does not exist in this PHP installation');
        }
        spl_autoload_register(array("System_Core_Loader", 'loadClass'));
    }

    /**
     * @param $className
     * @throws Exception
     */
    public static function loadClass($className) {
        if(in_array($className, System_Core_Loader::$loaderMap)) {
            return;
        }
        $fileArr = explode('_', $className);
        for($i = 0; $i < count($fileArr); $i++) {
            if($i < count($fileArr) - 1) {
                $fileArr[$i] = strtolower($fileArr[$i]);
            }
        }
        if($fileArr[0] == "system") {
            $filePath = ROOT_PATH . "/" . implode('/', $fileArr) . ".php";
        }
        else {
            $filePath = ROOT_PATH . "/app/". APP_NAME . '/' . implode('/', $fileArr) . ".php";
        }
        if(file_exists($filePath)) {
            System_Core_Loader::$loaderMap[] = $className;
            include_once $filePath;
        }
        else {
            throw new Exception("No File:".$filePath);
        }
    }

    /**
     * @param $filename
     * @throws Exception
     */
    public static function _securityCheck($filename) {
        if (preg_match('/[^a-z0-9\\/\\\\_.-]/i', $filename)) {
            throw new Exception('Security check: Illegal character in filename');
        }
    }
}