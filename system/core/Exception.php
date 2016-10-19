<?php

/**
 * Class System_Core_Exception
 */
class System_Core_Exception extends Exception {
    const SYS_UNKNOWN = -1;
    const SYS_UNKNOWN_ROUTE = -1;
    protected $messageList = array(
        self::SYS_UNKNOWN => '未知错误',
        self::SYS_UNKNOWN_ROUTE => "404",
    );

    public function __construct($code) {
        $code = isset($this->messageList[$code]) ? $code : self::SYS_UNKNOWN;
        $message = $this->messageList[$code];
        parent::__construct($message, $code);
    }

}