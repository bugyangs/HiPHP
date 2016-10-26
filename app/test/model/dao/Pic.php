<?php
/**
 * @name Dao_Pic
 * @desc 活动
 * @date   2016-10-11
 */
class Dao_Pic extends Core_DbBase {
    const IS_DEL_NO = 0;
    const IS_DEL_YES = 1;

    public static $TABLE = 'h_pic';
    /**
     * @var array
     */
    public $fields = array(
        'id' => array('field' => 'int', 'autoIncrement' => true, 'isPrimaryKey' => true),
        'name' => array('field' => 'char'),
        'url' => array('field' => 'char'),
        'add_time' => array('field' => 'int'),
        'mod_time' => array('field' => 'int'),
        'is_del' => array('field' => 'int'),
    );
}