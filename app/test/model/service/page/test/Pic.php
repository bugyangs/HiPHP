<?php
/**
 * @name Service_Page_Manage_Activity
 * @desc 添加活动
 * @date   2016-10-11
 */
class Service_Page_Test_Pic {

    private $objDataPic;

    /**
     * 构造函数
     */
    public function __construct(){
        $this->objDataPic = new Service_Data_Pic();
    }

    /**
     * @return mixed
     */
    public function execute() {
        $list = $this->objDataPic->getList(1, 10);
        return $list;
    }
}