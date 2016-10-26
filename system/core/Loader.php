<?php
/**
 * FileName: Loader.php
 * Author: zyf
 * Date: 16/9/19 下午4:17
 * Brief:
 */
class Core_Loader {
    public static $loaderMap = array();
    /**
     * @throws Exception
     */
    public static function registerAutoload() {
        if (!function_exists('spl_autoload_register')) {
            throw new Exception('spl_autoload does not exist in this PHP installation');
        }
        spl_autoload_register(array("Core_Loader", 'loadClass'));
    }

    /**
     * @param $className
     * @throws Exception
     */
    public static function loadClass($className) {
        if(in_array($className, Core_Loader::$loaderMap)) {
            return;
        }
        $fileArr = explode('_', $className);
        for($i = 0; $i < count($fileArr); $i++) {
            if($i < count($fileArr) - 1) {
                $fileArr[$i] = strtolower($fileArr[$i]);
            }
        }
//        $fileArr = array_map("strtolower", explode('_', $className));
//        $lastParamNum = (count($fileArr) - 1 > 0) ? (count($fileArr) - 1) : 0;
//        $fileArr[$lastParamNum] = ucwords($fileArr[$lastParamNum]);
        if($fileArr[0] == "core") {
            $filePath = SYSTEM_PATH . "/" . implode('/', $fileArr) . ".php";
        }
        else if($fileArr[0] == "lib") {
            $filePath = ROOT_PATH . "/" . implode('/', $fileArr) . ".php";
        }
        else if($fileArr[0] == "service" || $fileArr[0] == "dao") {
            $filePath = APP_PATH . "/model/" . implode('/', $fileArr) . ".php";
        }
        else {
            $filePath = APP_PATH . '/' . implode('/', $fileArr) . ".php";
        }
        if(file_exists($filePath)) {
            Core_Loader::$loaderMap[] = $className;
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