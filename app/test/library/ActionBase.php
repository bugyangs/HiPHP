<?php

abstract class Library_ActionBase extends Core_Action {
    const DEFAULT_HTTP_METHOD = Library_Config::HTTP_METHOD_GET;
    const DEFAULT_RENDER_TYPE = Library_Config::RESPONSE_AJAX;
    const DEFAULT_NEED_LOGIN  = false;

    protected $notFoundPage = "/common/not-found.tpl";
    protected $tpl = '';
    protected $arrInput;
    protected $httpMethod = self::DEFAULT_HTTP_METHOD;
    protected $renderType = self::DEFAULT_RENDER_TYPE;
    protected $needLogin  = self::DEFAULT_NEED_LOGIN;
    /**
     * @return bool
     */
    public function execute() {
        try {
            //用户公用参数
            $this->_initGlobal();
            $arrOutput = $this->_execute();
            $this->_return($arrOutput);
        }
        catch (Exception $e) {
            $this->_error($e);
        }
    }

    /**
     * @param $res
     *
     * @return bool
     */
    private function _return($arrOutput) {
        $this->_display($arrOutput);
    }

    /**
     * @param $e
     * @return bool
     */
    private function _error($e) {
        if($e instanceof Library_Exception) {
            $status = $e->getCode();
            $statusInfo = $e->getMessage();
            $response = Library_Response::responseAjaxMessage($status, $statusInfo, array());
        }
        else {
            $response = Library_Response::responseAjaxMessage(1, "系统错误", array());
        }
        $errorMessage = array(
            "code" => $e->getCode(),
            "message" => $e->getMessage(),
        );
        Core_Log::notice("ERROR : " . json_encode($errorMessage));
        $this->tpl = Library_Config::APP_NAME . $this->notFoundPage;
        $this->_display($response, true);
    }

    /**
     * @param $response
     * @param bool|false $isError
     * @return bool
     */
    private function _display($response, $isError = false) {
        if ($this->renderType == Library_Config::RESPONSE_SMARTY)
        {
            return Library_Response::smarty($response, $this->tpl);
        }
        else
        {
            if(!$isError) {
                $response = Library_Response::responseAjaxMessage(0, "OK", $response);
            }
            return Library_Response::ajax($response);
        }
    }

    /**
     *
     */
    protected function _initGlobal() {
//        if($this->needLogin) {
//            if(!Library_User::isLogin()) {
//                throw new Library_Exception(Library_Exception::Library_NO_LOGIN);
//            }
//            Library_Global::getInstance()->setPassId(Library_User::getPassId());
//        }
        //获取请求参数
        $this->arrInput = $this->_getInput($this->httpMethod);
        Core_Log::debug('request input :' . json_encode($this->arrInput));
    }

    /**
     * 获取请求参数
     * @param
     * @return
     */
    private function _getInput($httpMethod) {
        if ($httpMethod == Library_Config::HTTP_METHOD_POST)
        {
            $arrInput = $_POST;
        }
        else if ($httpMethod == Library_Config::HTTP_METHOD_GET)
        {
            $arrInput = $_GET;
        }
        else//request
        {
            $arrInput = $_REQUEST;
        }
        return $arrInput;
    }

    /**
     * 需要实现,获取要返回的数据
     */
    abstract function _execute();

}