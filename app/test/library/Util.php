<?php
/**
 * @name Library_Util
 * @desc APP工具类
 * @date   2016-10-11
 */
class Library_Util{
    /**
     * 生成唯一ID
     * @return int
     * @throws Exception
     */
    public static function generateUniqueId() {
        $uniqueId = new Dao_UniqueId();
        $id = $uniqueId->generateId();
        return $id;
    }


}
