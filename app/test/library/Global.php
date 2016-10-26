<?php
/**
 * @name Library_Global
 * @desc 全局变量
 * @date   2016-10-11
 */
class Library_Global {
    /**
     * @var Library_Global
     */
    private static $_instance = null;

    private $passId;

    /**
     * @return mixed
     */
    public function getPassId() {
        return $this->passId;
    }

    /**
     * @param $passId
     */
    public function setPassId($passId) {
        $this->passId = $passId;
    }



    /**
     * @return Library_Global
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
            self::init();
        }
        return self::$_instance;
    }

    /**
     * 构造函数
     */
    private function __construct() {
    }


    /**
     * 初始化
     */
    private function init() {
//        $arrRequest = Saf_SmartMain::getCgi();
//        $request    = $arrRequest['request_param'];
        //基本参数校验
//        $schema = array(
//        );
//        if (!Api_Common_Switch::isDebug())
//        {
//            $validInput = Api_Common_Valid::getFormattedInputs($request, $schema);
//        }
//        else
//        {
//            $validInput = $request;
//        }

    }
}
