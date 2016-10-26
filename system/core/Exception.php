<?php

/**
 * Class Core_Exception
 */
class Core_Exception extends Exception {
    const SYS_UNKNOWN = -1000;
    const SYS_UNKNOWN_ROUTE = -1001;
    const SYS_DB_CONNECT_FAIL = -1002;
    const SYS_CONFIG_FILE_NO_EXIST = -1003;
    const SYS_CONFIG_DATA_ERROR = -1004;
    protected $messageList = array(
        self::SYS_UNKNOWN => '未知错误',
        self::SYS_UNKNOWN_ROUTE => "404",
        self::SYS_DB_CONNECT_FAIL => "db fail",
        self::SYS_CONFIG_FILE_NO_EXIST => "config file not exist",
        self::SYS_CONFIG_DATA_ERROR => "config data error",
    );

    public function __construct($code, $message = "") {
        if(!$message) {
            $code = isset($this->messageList[$code]) ? $code : self::SYS_UNKNOWN;
            $message = $this->messageList[$code];
        }
        parent::__construct($message, $code);
    }

}