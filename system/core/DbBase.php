<?php

class Core_DbBase {

    /**
     * 数据库表名
     * @var string
     */
    public static $TABLE = "";

    /**
     * 数据库句柄
     * @var Core_Db_Db|null
     */
    private $db = null;

    /**
     * 数据表字段列表
     * 举例：$fields = array(
     *      'id'          => array('field'=>'int','autoIncrement' => true,'isPrimaryKey' => true),
     *      'name'        => array('field'=>'char','maxLength'=>100),
     *      'price'       => array('field'=>'float'),
     *      'addTime'     => array('field'=>'datetime','isAutoNow' => true),
     *      'loginDate'   => array('field'=>'date'),
     * );
     * 这里field支持4中类型：int(整型) | char(字符串) | float(浮点型) | datetime(日期时间如：'2015-01-01 12:12:12') | date(日期类型如：2015-01-01)
     * modTime在更新时，会自动更新为当前时间
     * addTime在数据插入时，会自动设置为当前时间
     * @var array
     */
    public $fields = array();

    /**
     * 字段schema
     * @var array
     */
    public static $__fields__ = array();

    protected static $southernHostRoom = array('nj03', 'nj02', 'sh01', 'hz');

    /**
     * 构造函数
     * @param string $dbName
     * @throws Core_Exception
     */
    public function __construct($clusterName = "default") {
        $this->db = Core_Db_Db::getInstance($clusterName);
    }

    /**
     * ping
     * @param sql $sql
     * @return mixed
     */
    public function ping() {
        return $this->db->mysql->ping();
    }

    /**
     * close
     * @param sql $sql
     * @return mixed
     */
    public function close() {
        return $this->db->db->close();
    }

    /**
     * query 查询
     * @param sql $sql
     * @return mixed
     */
    public function query($sql) {
        $ret = $this->db->query($sql);
        return $ret;
    }

    /**
     * 插入数据
     * @param array $row         带插入的数据，如：array('name'=>'aaa', 'age'=>20);
     * @param array $options     一些在前面的sql选项，如：array('DISTINCT', 'SQL_NO_CACHE');
     * @param array $onDuplicate 已存在时是否覆盖
     * @return mixed 成功：返回影响行数，失败：抛异常 Exception
     */
    public function insert($row, $options = null, $onDuplicate = null) {
        $arrDuplicate = array();
        if (!empty($onDuplicate)) {
            foreach ($onDuplicate as $key) {
                $arrDuplicate[$key] = $row[$key];
            }
        }
        $row = $this->filterFields($row, self::CHECK_TYPE_INSERT_AUTO);
        $ret = $this->db->insert(static::$TABLE, $row, $options, $arrDuplicate);
        Core_Log::debug("[DB Last SQL] " . $this->getLastSQL());
        if ($ret === false) {
            $errorMessage = array(
                'table'   => static::$TABLE,
                'rows'    => json_encode($row),
                'options' => json_encode($options),
                'onDup'   => json_encode($onDuplicate),
            );
            $this->handleError($errorMessage);
        }
        return $ret;
    }

    /**
     * 批量插入数据
     * @param array $rows        带插入的数据
     * @param array $options     一些在前面的sql选项，如：array('DISTINCT', 'SQL_NO_CACHE');
     * @param array $onDuplicate 已存在时是否覆盖
     * @return int
     */
    public function insertBatch($rows, $options = null, $onDuplicate = null) {
        $num = 0;
        foreach ($rows as $row) {
            $ret = $this->insert($row, $options, $onDuplicate);
            $num += $ret;
        }
        return $num;
    }


    /**
     * 物理删除记录
     * @param array|string $conditions 查询条件,例子: array('sex=' => 'male', 'age>' => 30)默认会按照AND连接多个条件 | "sex=male AND age > 30"
     * @param array        $options    一些在前面的sql选项，如：array('DISTINCT', 'SQL_NO_CACHE');
     * @param array        $appends    一些后置操作，如：array(‘ORDER BY modTime’, 'LIMIT 0, 10');
     * @return mixed 成功： 影响行数 失败： 抛异常 Exception
     */
    public function delete($conditions = null, $options = null, $appends = null) {
        $ret = $this->db->delete(static::$TABLE, $conditions, $options, $appends);
        Core_Log::trace("[DB SQL] " . $this->getLastSQL());

        if ($ret === false) {
            $errorMessage = array(
                'table'      => static::$TABLE,
                'conditions' => json_encode($conditions),
                'options'    => json_encode($options),
                'appends'    => json_encode($appends),
            );
            $this->handleError($errorMessage);
        }
        return $ret;
    }

    /**
     * 更新记录
     * @param array        $row        更新的列，如：array('name' => 'aaa', 'age'=> 30);
     * @param array|string $conditions 查询条件,例子: array('sex=' => 'male', 'age>' => 30)默认会按照AND连接多个条件 | "sex=male AND age > 30"
     * @param array        $options    一些在前面的sql选项，如：array('DISTINCT', 'SQL_NO_CACHE');
     * @param array        $appends    一些后置操作，如：array(‘ORDER BY modTime’, 'LIMIT 0, 10');
     * @return mixed 成功： 影响行数 失败：抛异常 Exception
     */
    public function update($row, $conditions = null, $options = null, $appends = null) {
        $row = $this->filterFields($row, self::CHECK_TYPE_UPDATE);
        $ret = $this->db->update(static::$TABLE, $row, $conditions, $options, $appends);
        Core_Log::debug("[DB SQL] " . $this->getLastSQL());
        if ($ret === false) {
            $errorMessage = array(
                'table'      => static::$TABLE,
                'row'        => json_encode($row),
                'conditions' => json_encode($conditions),
                'options'    => json_encode($options),
                'appends'    => json_encode($appends),
            );
            $this->handleError($errorMessage);
        }
        return $ret;
    }

    /**
     * 查询记录
     * @param array        $fields       查询的列
     * @param array|string $conditions   查询条件,例子: array('sex=' => 'male', 'age>' => 30)默认会按照AND连接多个条件 | "sex=male AND age > 30"
     * @param array        $options      一些在前面的sql选项，如：array('DISTINCT', 'SQL_NO_CACHE');
     * @param array        $appends      一些后置操作，如：array(‘ORDER BY modTime’, 'LIMIT 0, 10');
     * @param int          $fetchType    查询方式，一般默认就行
     * @return mixed 成功：查询结果  失败：抛异常 Exception
     */
    public function select($fields, $conditions = null, $options = null, $appends = null, $fetchType = Core_Db_Db::FETCH_ASSOC) {
        if (empty($fields) || $fields === "*") {
            $fields = $this->getFields();
        }

        if ($conditions && is_array($conditions)) {
            foreach ($conditions as $key => $item) {
                if (is_array($item)) {
                    $conditions[$key] = ' in (' . implode(',', $item) . ')';
                }
            }
        }
        $ret = $this->db->select(static::$TABLE, $fields, $conditions, $options, $appends, $fetchType);
        if ($ret) {
            foreach ($ret as $key => $item) {
                $ret[$key] = $this->filterFields($item);
            }
        }
        Core_Log::debug("[DB SQL] " . $this->getLastSQL());
        if ($ret === false) {
            $errorMessage = array(
                'table'      => static::$TABLE,
                'fields'     => json_encode($fields),
                'conditions' => json_encode($conditions),
                'options'    => json_encode($options),
                'appends'    => json_encode($appends),
            );
            $this->handleError($errorMessage);
        }
        return $ret;
    }

    /**
     * 处理数据库错误
     * @param array $args
     * @throws Exception
     */
    private function handleError($args = array()) {
        $res = $this->db->error();
        if (!empty($res)) {
            $msg = array(
                'sql'   => $this->getLastSQL(),
                'error' => $this->db->error(),
            );
            throw new Exception(json_encode($msg));
        }
        else {
            $error = "";
            foreach ($args as $k => $v) {
                $error .= "[$k : $v]";
            }
            $msg = $error;
            throw new Exception($msg);
        }
    }

    /**
     * 查询记录数
     * @param array|string $conditions 查询条件,例子: array('sex=' => 'male', 'age>' => 30)默认会按照AND连接多个条件 | "sex=male AND age > 30"
     * @param array        $options    一些在前面的sql选项，如：array('DISTINCT', 'SQL_NO_CACHE');
     * @param array        $appends    一些后置操作，如：array(‘ORDER BY modTime’, 'LIMIT 0, 10');
     * @return mixed 成功：查询结果  失败：抛异常 Exception
     */
    public function selectCount($conditions = null, $options = null, $appends = null) {
        $ret = $this->db->selectCount(static::$TABLE, $conditions, $options, $appends);
        Core_Log::debug("[DB SQL] " . $this->getLastSQL());
        if ($ret === false) {
            $this->handleError(
                array(
                    'table'      => static::$TABLE,
                    'conditions' => json_encode($conditions),
                    'options'    => json_encode($options),
                    'appends'    => json_encode($appends),
                )
            );
        }
        return $ret;
    }

    /**
     * 获取刚插入记录id
     * @return mixed 成功： 新记录ID  失败： 返回false
     */
    public function getInsertID() {
        return $this->db->getInsertID();
    }

    /**
     * 获取该模型的所有字段列表
     *
     * @return string
     */
    protected function getFields() {
        return array_keys($this->fields);
    }

    /**
     * 获取上个真正被执行的SQL语句
     * @return mixed 成功： 返回上个真正被执行的SQL语句 失败：null
     */
    public function getLastSQL() {
        $ret = $this->db->getLastSQL();
        return $ret;
    }

    /**
     * 获取上个更新操作影响的行数
     * @return mixed 成功： 返回非负数,失败： 返回-1
     */
    public function getAffectedRows() {
        $ret = $this->db->getAffectedRows();
        return $ret;
    }

    /**
     * 设置或查询当前自动提交状态
     * @param null $isAuto
     * @return mixed
     */
    public function autoCommit($isAuto = null) {
        $ret = $this->db->autoCommit($isAuto);
        return $ret;
    }

    /**
     * 开始一个事务
     * @return mixed bool值，指出是否设置成功
     */
    public function startTransaction() {
        $ret = $this->db->startTransaction();
        return $ret;
    }

    /**
     * 提交当前事务
     * @return mixed 指出是否设置成功
     */
    public function commit() {
        $ret = $this->db->commit();
        return $ret;
    }

    /**
     * 回滚当前事务
     * @return mixed 指出是否设置成功
     */
    public function rollback() {
        $ret = $this->db->rollback();
        return $ret;
    }

    /**
     * 基于当前连接的字符集escape字符串
     * @param $string
     * @return mixed
     */
    public function escapeString($string) {
        $ret = $this->db->escapeString($string);
        return $ret;
    }

    /**
     * 设置和查询当前连接的字符集
     * @param null $name NULL表示查询，字符串表示设置
     * @return mixed 查询时，返回当前字符集的名称设置;设置时，返回bool值
     */
    public function charset($name = null)
    {
        $ret = $this->db->charset($name);
        return $ret;
    }

    /**
     * 获取当前db中存在的表(注：在人工拆表的db中，本函数很有用)
     * @param null $pattern 设置表名pattern
     * @param null $dbname  设置db名称，默认为当前db
     * @return mixed 成功则返回表名组成的数组，失败返回false
     */
    public function getTables($pattern = null, $dbname = null)
    {
        $ret = $this->db->getTables($pattern, $dbname);
        return $ret;
    }

    /**
     * 查询指定表是否存在
     * @param      $tableName  表名称
     * @param null $dbName     设置db名称，默认为当前db
     * @return mixed 成功则返回bool值，失败返回NULL
     */
    public function isTableExists($tableName, $dbName = null)
    {
        $ret = $this->db->isTableExists($tableName, $dbName);
        return $ret;
    }

    /**
     * -------------------可爱的分界线，上面是基本方法，下面是包装的方法，简化派生类操作 ----------------
     */

    const PAGE_DEFAULT     = 1;
    const PAGESIZE_DEFAULT = 10;

    const CHECK_TYPE_INSERT_AUTO = 1; //自增插入操作
    const CHECK_TYPE_SELECT      = 10; //查询操作
    const CHECK_TYPE_UPDATE      = 11; //更新操作

    /**
     * 过滤掉不在field中的字段以及校验参数类型
     * @param array $params
     * @param int   $checkType
     * @return array
     * @throws Exception
     */
    private function filterFields($params, $checkType = self::CHECK_TYPE_SELECT)
    {
        if (empty($params))
        {
            return $params;
        }

        if ($checkType == self::CHECK_TYPE_UPDATE && !isset($params['modTime']))
        {
            $this->handleModOrAddTime($params, 'modTime');
        }
        elseif ($checkType == self::CHECK_TYPE_INSERT_AUTO && !isset($params['addTime']))
        {
            $this->handleModOrAddTime($params, 'addTime');
        }

        foreach ($params as $field => $value)
        {
            if (!isset($this->fields[$field]))
            {
                unset($params[$field]);
                continue;
            }
            elseif ($this->fields[$field])
            {
                //类型转换
                switch ($this->fields[$field]['field'])
                {
                    case 'int':
                        $params[$field] = intval($params[$field]);
                        break;
                    case 'float':
                        $params[$field] = floatval($params[$field]);
                        break;
                    case 'char':
                        $params[$field] = strval($params[$field]);
                        if (isset($this->fields[$field]['maxLength']) && $this->fields[$field]['maxLength'] && Core_String::getStringLength($params[$field]) > $this->fields[$field]['maxLength'])
                        {
                            throw new Exception('Table Fields Check : The [' . $field . ']\'s value is too long, max Length:' . $this->fields[$field]['maxLength']);
                        }
                        break;
                    case 'datetime':
                    case 'date':
                        if ($checkType != self::CHECK_TYPE_SELECT && strtotime($params[$field]) === false)
                        {
                            throw new Exception('Table Fields Check : The [' . $field . ']\'s value need datetime type!');
                        }
                        break;
                    default:
                        ;
                }
                //在数据插入或更新时，对自增主键进行过滤
                if (isset($this->fields[$field]['isPrimaryKey']) && $this->fields[$field]['isPrimaryKey']
                    && isset($this->fields[$field]['autoIncrement']) && $this->fields[$field]['autoIncrement']
                    && $checkType != self::CHECK_TYPE_SELECT
                )
                {
                    unset($params[$field]);
                    continue;
                }
            }
        }
        return $params;
    }

    /**
     * 自动处理更新时间和添加时间
     * @param $params
     * @param $fieldName
     */
    private function handleModOrAddTime(&$params, $fieldName)
    {
        if (in_array($fieldName, array('addTime', 'modTime')))
        {
            if (isset($this->fields[$fieldName]))
            {
                if ($this->fields[$fieldName]['field'] == 'int')
                {
                    $params[$fieldName] = time();
                }
                elseif ($this->fields['addTime']['field'] == 'datetime')
                {
                    $params[$fieldName] = date("Y-m-d H:i:s");
                }
            }
        }
    }

    /**
     * 格式化排序,用于数据库查询
     * @param $arrOrderBy
     * @return array
     */
    protected function getFormattedOrderBy($arrOrderBy)
    {
        $appends = array();
        if (!empty($arrOrderBy))
        {
            $strOrderBy = 'ORDER BY ';
            if (!empty($arrOrderBy['desc']))
            {
                if (is_array($arrOrderBy['desc']))
                {
                    foreach ($arrOrderBy['desc'] as $strDesc)
                    {
                        $strOrderBy .= $strDesc . " DESC, ";
                    }
                }
                elseif (is_string($arrOrderBy['desc']))
                {
                    $strOrderBy .= $arrOrderBy['desc'] . " DESC, ";
                }
            }
            if (!empty($arrOrderBy['asc']))
            {
                if (is_array($arrOrderBy['asc']))
                {
                    foreach ($arrOrderBy['asc'] as $strDesc)
                    {
                        $strOrderBy .= $strDesc . " ASC, ";
                    }
                }
                elseif ($arrOrderBy['asc'])
                {
                    $strOrderBy .= $arrOrderBy['asc'] . " ASC, ";
                }
            }
            $strOrderBy = substr($strOrderBy, 0, strlen($strOrderBy) - 2);
            $appends[]  = $strOrderBy;
        }
        return $appends;
    }

    /**
     * 根据条件获取数据列表
     * @param array|string $conditions  查询条件,例子: array('sex=' => 'male', 'age>' => 30)默认会按照AND连接多个条件 | "sex=male AND age > 30"
     * @param string       $fields      查询的列表
     * @param int          $intPage     起始页
     * @param int          $intPageSize 每页大小
     * @param null         $orderBy     排序操作，如：array(‘desc’=>'id', 'asc' => 'addTime');
     * @param null         $options     一些在前面的sql选项，如：array('DISTINCT', 'SQL_NO_CACHE');
     * @return mixed 成功：查询结果列表  失败：抛异常 Exception
     */
    public function getListByCondition($conditions, $fields = '*', $intPage = 1, $intPageSize = 10,  $orderBy = null, $options = null)
    {
        $appends = array();
        if (!empty($orderBy))
        {
            $appends = $this->getFormattedOrderBy($orderBy);
        }
        $limitString = Core_Paging::getLimit($intPage, $intPageSize, true);
        $appends[]   = $limitString;
        return $this->select($fields, $conditions, $options, $appends);
    }

    /**
     * 根据id查询
     * @param int $intId 对象id
     * @param array|string $fields 要查询的列
     *
     * @return mixed
     */
    public function getById($intId, $fields='*'){
        $conditions = array(
            'id=' => $intId,
        );
        return $this->select($fields, $conditions);
    }
    /**
     * 根据ids获取数据
     * @param       $ids
     * @param null  $arrFields
     * @param int   $intPageSize
     * @param int   $intPage
     * @param array $arrOrderBy
     * @param array $options
     * @return array|mixed
     */
    public function getListByIds($ids, $arrFields = null, $intPageSize = 10, $intPage = 1, $arrOrderBy = null, $options = null)
    {
        if (empty($ids))
        {
            return array();
        }
        foreach ($ids as $key => $id)
        {
            $ids[$key] = intval($id);
        }
        if (count($ids) == 1)
        {
            $conditions = array(
                'id=' => reset($ids),
            );
        }
        else
        {
            $conditions = 'id in (' . implode(',', $ids) . ')';
        }
        return $this->getListByCondition($conditions, $arrFields, $intPage, $intPageSize, $arrOrderBy, $options);
    }

    /**
     * 根据IDS进行数据删除
     * @param $ids
     * @return array|mixed
     */
    public function deleteByIds($ids)
    {
        if (empty($ids))
        {
            return array();
        }
        foreach ($ids as $key => $id)
        {
            $ids[$key] = intval($id);
        }
        if (count($ids) == 1)
        {
            $conditions = array(
                'id=' => reset($ids),
            );
        }
        else
        {
            $conditions = 'id in (' . implode(',', $ids) . ')';
        }
        return $this->delete($conditions);
    }
}
