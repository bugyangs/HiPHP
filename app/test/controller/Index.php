<?php

class Controller_Index extends Library_ControllerBase {

    /**
     * @var array
     */
    public $actions = array(
        'test' => 'actions/index/Test.php',
        'index' => 'actions/index/Index.php',
    );
}