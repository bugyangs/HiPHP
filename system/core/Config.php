<?php

/**
 * Class Core_Config
 */
class Core_Config {
    /**
     * @var
     */
    private static $_instance;
    private static $_staticConfig;
    private $_fileName;

    /**
     * @param $fileName
     */
    private function __construct($fileName) {
        $filePath = APP_PATH . "/config/" . $fileName . ".php";
        if(!file_exists($filePath)) {
            throw new Core_Exception(Core_Exception::SYS_CONFIG_FILE_NO_EXIST);
        }
        require_once($filePath);
        if(!isset($config) || !is_array($config)) {
            throw new Core_Exception(Core_Exception::SYS_CONFIG_FILE_NO_EXIST);
        }
        $this->_fileName = $fileName;
        self::$_staticConfig[$fileName] = $config;
    }

    /**
     * @param $name
     * @return bool
     */
    public function item($name = "") {
        if($name == "") {
            return self::$_staticConfig[$this->_fileName];
        }
        else {
            if(self::$_staticConfig[$this->_fileName] && self::$_staticConfig[$this->_fileName][$name]) {
                return self::$_staticConfig[$this->_fileName][$name];
            }
            else {
                return false;
            }
        }
    }

    /**
     * @param $fileName
     * @return Core_Config
     */
    public static function getInstance($fileName) {
        if(!self::$_instance[$fileName]) {
            self::$_instance[$fileName] = new self($fileName);
        }
        return self::$_instance[$fileName];
    }

}