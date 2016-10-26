<?php
/**
 * @name Library_User
 * @desc 登录相关函数
 * @date   2016-10-11
 */
class Library_User {
    /**
     * @return bool
     */
    public static function isLogin() {
        $userInfo = Saf_SmartMain::getUserInfo();
        $passId = isset($userInfo['uid'])?$userInfo['uid']:0;
        return ($passId > 0)?true:false;
    }

    /**
     * @return int
     */
    public static function getPassId() {
        $userInfo = Saf_SmartMain::getUserInfo();
        return isset($userInfo['uid'])?$userInfo['uid']:0;
    }

    /**
     * @return string
     */
    public static function getUserName() {
        $userInfo = Saf_SmartMain::getUserInfo();
        return isset($userInfo['uname'])?$userInfo['uname']:'';
    }

    /**
     * @return string
     */
    public static function getUserPhone() {
        $userInfo = Saf_SmartMain::getUserInfo();
        return isset($userInfo['securemobil'])?$userInfo['securemobil']:'';
    }
} 