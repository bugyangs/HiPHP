<?php

/**
 * Class Controller_Index
 */
class Action_Test extends Library_ActionBase {

    /**
     * 执行
     */
    public function _execute() {
        $objService = new Service_Page_Test_Pic();
        return $objService->execute();
    }
}