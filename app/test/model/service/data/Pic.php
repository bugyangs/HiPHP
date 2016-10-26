<?php
/**
 * @name Service_Data_Activity
 * @desc 活动
 * @date   2016-10-11
 */
class Service_Data_Pic {

    private $objDaoPic;

    /**
     * 构造函数
     */
    public function __construct(){
        $this->objDaoPic = new Dao_Pic();
    }

    /**
     * 获取图片列表
     * @param $page
     * @param $pageSize
     * @return mixed
     */
    public function getList($page, $pageSize) {
        return $this->objDaoPic->getListByCondition(array(), '*', $page, $pageSize);
    }
}