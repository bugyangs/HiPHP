<?php
/**
 * @name Library_Response
 * @desc 统一的返回包处理类，统一封装ajax和smarty返回
 * @date   2016-10-11
 */
class Library_Response {
    const MSG_TYPE_DEFAULT = 1;
    const MSG_TYPE_AR = 2;
    /**
     * @param Exception|array $result
     * @return bool
     */
    public static function ajax($result, $responseType="application/json") {
        header("Content-type: ". $responseType. "; charset=utf-8");
        echo json_encode($result);
        Core_Log::debug("request response: " . json_encode($result));
        return true;
    }

    /**
     * 模板渲染
     *
     * @param $result
     * @param $tplName
     *
     * @return bool
     */
    public static function smarty($result, $tplName) {
        $tplName = MAIN_APP . $tplName;
        $result = array(
            'tplData'           => $result,
            'appName'          => Library_Config::APP_NAME,
        );
        header("Content-type: text/html; charset=utf-8");
        //禁止页面缓存
        header("Cache-Control:no-cache,must-revalidate,no-store"); //这个no-store加了之后，Firefox下有效
        header("Pragma:no-cache");
        header("Expires:-1");
        Core_Log::debug("request response: " . json_encode($result));
        $tpl = Bd_TplFactory::getInstance();
        foreach($result as $key => $value)
        {
            $tpl->assign($key, $value);
        }
        $tpl->display($tplName);
        return true;
    }

    /**
     * 跳转到错误页
     *
     * @param string $url
     */
    public static function to404($url = 'http://www.baidu.com/search.html')
    {
        Header('Location: ' . $url);
        exit;
    }

    /**
     * 跳转到固定地址
     *
     * @param $url
     */
    public static function redirect($url)
    {
        Header('Location: ' . $url);
        exit;
    }

    /**
     * @param $code
     * @param $message
     * @param $data
     * @return array
     */
    public static function responseAjaxMessage($code, $message, $data) {
        return $response = array(
            'status'     => $code,
            'statusInfo' => $message,
            'data'       => $data,
            't' => time(),
        );
    }
}