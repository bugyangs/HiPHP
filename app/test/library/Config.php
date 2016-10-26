<?php
/**
 * @name Library_Config
 * @desc APP公共配置类
 * @date   2016-10-11
 */
class Library_Config{
    const APP_NAME = "market";
    /*
     * http请求的method,避免使用request
     */
    const HTTP_METHOD_GET     = 'GET';
    const HTTP_METHOD_POST    = 'POST';
    const HTTP_METHOD_REQUEST = 'REQUEST';

    const RESPONSE_AJAX   = 'ajax';
    const RESPONSE_SMARTY = 'smarty';


}
