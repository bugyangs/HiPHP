<?php
/**
 * @file $FILE NAME$
 * @author $DoxygenToolkit_authorName$
 * @date 2011/01/20 14:19:45
 * @brief  $Revision$
 *  
 */

class Core_Db_SQLAssembler implements Core_Db_ISQL
{
    const LIST_COM = 0;
    const LIST_AND = 1;
    const LIST_SET = 2;
    const LIST_VAL = 3;

    private $sql = NULL;
    private $db = NULL;

    public function __construct(Core_Db_Db $db)
    {
        $this->db = $db;
    }

    /**
    * @brief 获取sql
    *
    * @return 
    */
    public function getSQL()
    {
        return $this->sql;
    }

    /**
    * @brief 获取select语句
    *
    * @param $tables 表名
    * @param $fields 字段名
    * @param $conds 条件
    * @param $options 选项
    * @param $appends 结尾操作
    *
    * @return 
    */
    public function getSelect($tables, $fields, $conds = NULL, $options = NULL, $appends = NULL)
    {
        $sql = 'SELECT ';
        // 1. options
        if($options !== NULL)
        {
            $options = $this->__makeList($options, Core_Db_SQLAssembler::LIST_COM, ' ');
            if(!strlen($options))
            {
                $this->sql = NULL;
                return NULL;
            }
            $sql .= "$options ";
        }

        // 2. fields
        $fields = $this->__makeList($fields, Core_Db_SQLAssembler::LIST_COM);
        if(!strlen($fields))
        {
            $this->sql = NULL;
            return NULL;
        }
        $sql .= "$fields FROM ";

        // 3. from
        $tables = $this->__makeList($tables, Core_Db_SQLAssembler::LIST_COM);
        if(!strlen($tables))
        {
            $this->sql = NULL;
            return NULL;
        }
        $sql .= $tables;

        // 4. conditions
        if($conds !== NULL && $conds)
        {
            $conds = $this->__makeList($conds, Core_Db_SQLAssembler::LIST_AND);
            if(!strlen($conds))
            {
                $this->sql = NULL;
                return NULL;
            }
            $sql .= " WHERE $conds";
        }
        // 5. other append
        if($appends !== NULL)
        {
            $appends = $this->__makeList($appends, Core_Db_SQLAssembler::LIST_COM, ' ');
            if(!strlen($appends))
            {
                $this->sql = NULL;
                return NULL;
            }
            $sql .= " $appends";
        }

        $this->sql = $sql;
        return $sql;
    }

    /**
    * @brief 获取update语句
    *
    * @param $table 表名
    * @param $row 字段
    * @param $conds 条件
    * @param $options 选项
    * @param $appends 结尾操作
    *
    * @return 
    */
    public function getUpdate($table, $row, $conds = NULL, $options = NULL, $appends = NULL)
    {
        if(empty($row))
        {
            return NULL;
        }
        return $this->__makeUpdateOrDelete($table, $row, $conds, $options, $appends);
    }

    /**
    * @brief 获取delete语句
    *
    * @param $table
    * @param $conds
    * @param $options
    * @param $appends
    *
    * @return 
    */
    public function getDelete($table, $conds = NULL, $options = NULL, $appends = NULL)
    {
        return $this->__makeUpdateOrDelete($table, NULL, $conds, $options, $appends);
    }

    private function __makeUpdateOrDelete($table, $row, $conds, $options, $appends)
    {
        // 1. options
        if($options !== NULL)
        {
            if(is_array($options))
            {
                $options = implode(' ', $options);
            }
            $sql = $options;
        }

        // 2. fields
        // delete
        if(empty($row))
        {
            $sql = "DELETE $options FROM $table ";
        }
        // update
        else
        {
            $sql = "UPDATE $options $table SET ";
            $row = $this->__makeList($row, Core_Db_SQLAssembler::LIST_SET);
            if(!strlen($row))
            {
                $this->sql = NULL;
                return NULL;
            }
            $sql .= "$row ";
        }

        // 3. conditions
        if($conds !== NULL)
        {
            $conds = $this->__makeList($conds, Core_Db_SQLAssembler::LIST_AND);
            if(!strlen($conds))
            {
                $this->sql = NULL;
                return NULL;
            }
            $sql .= "WHERE $conds ";
        }

        // 4. other append
        if($appends !== NULL)
        {
            $appends = $this->__makeList($appends, Core_Db_SQLAssembler::LIST_COM, ' ');
            if(!strlen($appends))
            {
                $this->sql = NULL;
                return NULL;
            }
            $sql .= $appends;
        }

        $this->sql = $sql;
        return $sql;
    }

/**
 * get multi insert sql
 * 
 * @param string table name
 * @param array table fileds
 * @param array values, array(array(),array(),)
 * @return string sql
 *
 */
    public function getMultiInsert($table, $fields, $values, $options = null, $onDup = null)
    {
        $sql = 'INSERT ';
        if (!strlen($table))
        {
            $this->sql = null;
            return null;
        }

        // 1. options
        if($options !== null)
        {
            $options = $this->__makeList($options, Core_Db_SQLAssembler::LIST_COM, ' ');
            if(!strlen($options))
            {
                $this->sql = null;
                return null;
            }
            $sql .= "$options ";
        }

        // table
        $sql .= "$table(";

        // 2. fields
        $fields = $this->__makeList($fields, Core_Db_SQLAssembler::LIST_COM);
        if (!strlen($fields))
        {
            return null;
        }
        $sql .= "$fields)";

        //3 .values
        //array(array(1,2,3) array(4,5,6))
        if (!is_array($values))
        {
            $this->sql = null;
            return null;
        }

        $count = 0;

        if(is_array($fields)){
            $count = count($fields);
        }
        else{
            $count = count( explode(",", $fields));
        }

        $insert_values = "";
        foreach ($values as $value)
        {
            $val = $this->__makeList($value, Core_Db_SQLAssembler::LIST_VAL);
            $size = count( explode(",", $val));
            if(!strlen($val) || $size != $count)
            {
                $this->sql = null;
                return null; 
            } 
            $insert_values .= "$val, "; 
        }
        
        if (!strlen($insert_values))
        {
            $this->sql = null;
            return null;
        }
        $sql .= " VALUES ". substr($insert_values, 0, strlen($insert_values) - 2);

        //5. ondup
        if(!empty($onDup))
        {
            $sql .= ' ON DUPLICATE KEY UPDATE ';
            $onDup = $this->__makeList($onDup, Core_Db_SQLAssembler::LIST_SET);
            if(!strlen($onDup))
            {
                $this->sql = null;
                return null;
            }
            $sql .= $onDup;
        }
       
        $this->sql = $sql;
        return  $sql;
    }



    /**
    * @brief 获取insert语句
    *
    * @param $table 表名
    * @param $row 字段
    * @param $options 选项
    * @param $onDup 键冲突时的字段值列表
    *
    * @return 
    */
    public function getInsert($table, $row, $options = NULL, $onDup = NULL)
    {
        $sql = 'INSERT ';

        // 1. options
        if($options !== NULL)
        {
            if(is_array($options))
            {
                $options = implode(' ', $options);
            }
            $sql .= "$options ";
        }

        // 2. table
        $sql .= "$table SET ";

        // 3. clumns and values
        $row = $this->__makeList($row, Core_Db_SQLAssembler::LIST_SET);
        if(!strlen($row))
        {
            $this->sql = NULL;
            return NULL;
        }
        $sql .= $row;

        if(!empty($onDup))
        {
            $sql .= ' ON DUPLICATE KEY UPDATE ';
            $onDup = $this->__makeList($onDup, Core_Db_SQLAssembler::LIST_SET);
            if(!strlen($onDup))
            {
                $this->sql = NULL;
                return NULL;
            }
            $sql .= $onDup;
        }
        $this->sql = $sql;
        return $sql;
    }

    private function __makeList($arrList, $type = Core_Db_SQLAssembler::LIST_SET, $cut = ', ')
    {
        if(is_string($arrList))
        {
            return $arrList;
        }

        $sql = '';

        // for set in insert and update
        if($type == Core_Db_SQLAssembler::LIST_SET)
        {
            foreach($arrList as $name => $value)
            {
                if(is_int($name))
                {
                    $sql .= "$value, ";
                }
                else
                {
                    $value = $this->formatValue($value);
                    $sql .= "$name=$value, ";
                }
            }
            $sql = substr($sql, 0, strlen($sql) - 2);
        }
        // for where conds
        else if($type == Core_Db_SQLAssembler::LIST_AND)
        {
            foreach($arrList as $name => $value)
            {
                if(is_int($name))
                {
                    if(is_array($value)){
                        $size = count($value);
                        if($size == 2){
                            $name = $value[0];
                            $value = $this->formatValue($value[1]);
                            $sql .= "($name $value) AND ";
                        }
                        if($size == 3){
                            $name = $value[0];
                            $opt = $value[1];
                            if("in" == trim(strtolower($opt)) || "not in" == trim(strtolower($opt))){
                                $items = explode(",", $value[2]);
                                $fvalue = "";
                                if(!is_array($items)){
                                    $items = array($items);
                                }
                                foreach($items as $item){
                                    $fvalue .= $this->formatValue($item) . ", ";
                                }
                                $fvalue = substr($fvalue, 0, strlen($fvalue) - 2);
                                $value = "( $fvalue )";
                            }else{
                                $value = $this->formatValue($value[2]);
                            }
                            $sql .= "($name $opt $value) AND ";
                        }
                    }else{
                        $sql .= "($value) AND ";
                    }
                }
                else
                {
                    $value = $this->formatValue($value);
                    $sql .= "($name $value) AND ";
                }
            }
            $sql = substr($sql, 0, strlen($sql) - 5);
        }
        //for multi insert
        else if($type == Core_Db_SQLAssembler::LIST_VAL)
        {
            foreach($arrList as $value)
            {
                $value = $this->formatValue($value);
                $sql .= "$value, ";
            }
           $sql = "(" .substr($sql, 0, strlen($sql) - 2). ")"; 
        }
        else
        {
            if(is_array($arrList))
            {
                $sql = implode($cut, $arrList);
            }
        }

        return $sql;
    }

   /**
    *
    * @param value
    * @return formated value
    *
    **/ 
    private function formatValue($value){
        if(!is_int($value))
        {
            if($value === null)
            {
                $value = 'NULL';
            }
            else
            {
                $value = '\''.$this->db->escapeString($value).'\'';
            }
        }
        
        return $value;
    }
}
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */