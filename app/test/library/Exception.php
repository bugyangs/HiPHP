<?php
/**
 * @name Library_Exception
 * @desc 异常
 * @date   2016-10-11
 */
class Library_Exception extends Exception {
    // 系统级错误代码
    const SYS_UNKNOWN = 10000;
    const SYS_ERROR = 10001;
    const SYS_PARAM_ERROR = 10002;
    const SYS_BUSY_ERROR = 10003;
    //应用错误
    const Library_NO_LOGIN = 20001;
    //ar相关
    const Library_NO_ACTIVITY = 20101;
    const Library_ACTIVITY_OVER = 20101;
    const Library_BUSINESS_OVER = 20102;
    const Library_NO_PRIZE = 20103;
    const Library_ACTIVITY_ADD_FAIL = 20104;
    //自定义
    const Library_CUSTOM = 30000;
    const Library_CUSTOM_PARAM_VALID = 30001;
    /**
     * 10203，第一位：1系统错误、2应用错误、3自定义，第二三位（02）：业务，第四五位（03）：具体错误
     */
    protected $messageList = array(
        self::SYS_UNKNOWN => '未知错误',
        self::SYS_ERROR => '系统错误',
        self::SYS_PARAM_ERROR => '参数非法',
        self::SYS_BUSY_ERROR => '系统繁忙，请稍候再试',

        self::Library_NO_LOGIN => "未登录",
        self::Library_NO_ACTIVITY => "活动不存在",
        self::Library_ACTIVITY_OVER => "活动已结束",
        self::Library_BUSINESS_OVER => "商家不存在",
        self::Library_NO_PRIZE => "未中奖",
        self::Library_ACTIVITY_ADD_FAIL => "添加失败",

    );

    /**
     * @param string $code
     * @param string $customMessage
     */
    public function __construct($code, $customMessage = "")
    {
        if(intval($code / Library_Exception::Library_CUSTOM) == 1) {
            $message = $customMessage;
        }
        else {
            $code = isset($this->messageList[$code]) ? $code : self::SYS_UNKNOWN;
            $message = $this->messageList[$code];
        }
        parent::__construct($message, $code);
    }

}