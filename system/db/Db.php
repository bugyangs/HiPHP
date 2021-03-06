<?php

class Core_Db_Db {

    const FETCH_RAW = 0;    // return raw mysqli_result
    const FETCH_ROW = 1;    // return numeric array
    const FETCH_ASSOC = 2;  // return associate array
    const FETCH_OBJ = 3;    // return Bd_DBResult object

    private $mysql = NULL;
    private $sqlAssember = NULL;
    private $lastSql = "";
    private $totalCost = 0;
    private $lastCost = 0;
    private $arrOptions = array();

    public $isConnected = false;
    public $dbConfig = array();
    public $retryTime = 0;


    /**
     * 构造函数
     */
    public function __construct() {
        $this->mysql = mysqli_init();
    }

    /**
     * @param $host
     * @param null $userName
     * @param null $password
     * @param null $dbName
     * @param null $port
     * @param int $retryTime
     * @return bool
     */
    public function connect($host, $userName = null, $password = null, $dbName = null, $port = null, $retryTime = 0) {
        $this->dbConfig = array(
            "host" => $host,
            "userName" => $userName,
            "password" => $password,
            "dbName" => $dbName,
            "port" => $port,
        );
        $this->retryTime = $retryTime;
        for ($i=0; $i <= $this->retryTime; $i++) {
            $this->isConnected = $this->mysql->real_connect($host, $userName, $password, $dbName, $port);
            if($this->isConnected) {
                return $this->isConnected;
            }
        }
        return $this->isConnected;
    }

    /**
     * @param $table
     * @param $fields
     * @param $condition
     * @param null $option
     * @param array $append
     * @param int $fetchType
     * @return array|bool|Core_Db_DBResult|mysqli_result
     */
    public function select($table,
                           $fields,
                           $condition,
                           $option = null,
                           $append = array(),
                           $fetchType = Core_Db_Db::FETCH_ASSOC) {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getSelect($table, $fields, $condition, $option, $append);
        if(!$sql) {
            return false;
        }
        return $this->query($sql, $fetchType);
    }

    public function selectCount($table, $condition = null, $option = null, $append = null) {
        $this->__getSQLAssember();
        $fields = "COUNT(*)";
        $sql = $this->sqlAssember->getSelect($table, $fields, $condition, $option, $append);
        if(!$sql) {
            return false;
        }
        $res = $this->query($sql, Core_Db_Db::FETCH_ROW);
        if($res === false) {
            return false;
        }
        return intval($res[0][0]);
    }

    /**
     * @param $table
     * @param $row
     * @param null $option
     * @param null $onDup
     * @return bool|int
     */
    public function insert($table, $row, $option = null, $onDup = null) {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getInsert($table, $row, $option, $onDup);
        if(!$sql || !$this->query($sql))
        {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    /**
     * @param $table
     * @param $row
     * @param null $condition
     * @param null $option
     * @param null $append
     * @return bool|int
     */
    public function update($table, $row, $condition = null, $option = null, $append = null) {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getUpdate($table, $row, $condition, $option, $append);
        if(!$sql || !$this->update($table, $row, $condition, $option, $append)) {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    /**
     * @param $table
     * @param null $condition
     * @param null $option
     * @param null $append
     * @return bool|int
     */
    public function delete($table, $condition = null, $option = null, $append = null) {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getDelete($table, $condition, $option, $append);
        if(!$sql || !$this->query($sql)) {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    /**
     * @param $sql
     * @param $fetchType
     * @return array|bool|Core_Db_DBResult|mysqli_result
     */
    public function query($sql, $fetchType = Core_Db_Db::FETCH_ASSOC) {
        $this->lastSql = $sql;
        $beg = intval(microtime(true)*1000000);
        $res = $this->mysql->query($sql);
        $this->lastCost = intval(microtime(true)*1000000) - $beg;
        $this->totalCost += $this->lastCost;
        if(is_bool($res) || $res === null) {
            $ret = ($res == true);
            if(!$ret) {
                //log
            }
        }
        else {
            $info['query_count'] = $res->num_rows;
            switch($fetchType)
            {
                case Core_Db_Db::FETCH_OBJ:
                    $ret = new Core_Db_DBResult($res);
                    break;

                case Core_Db_Db::FETCH_ASSOC:
                    $ret = array();
                    while($row = $res->fetch_assoc())
                    {
                        $ret[] = $row;
                    }
                    $res->free();
                    break;

                case Core_Db_Db::FETCH_ROW:
                    $ret = array();
                    while($row = $res->fetch_row())
                    {
                        $ret[] = $row;
                    }
                    $res->free();
                    break;

                default:
                    $ret = $res;
                    break;
            }
        }
        return $ret;
    }

    /**
     * @brief 获取Insert_id
     *
     * @return
     */
    public function getInsertID() {
        return $this->mysql->insert_id;
    }

    /**
     * @brief 获取受影响的行数
     *
     * @return
     */
    public function getAffectedRows() {
        return $this->mysql->affected_rows;
    }

    public function close() {
        if(!$this->isConnect) {
            return ;
        }
        $this->isConnect = false;
        $this->mysql->close();
    }

    // FIXME: there is bug in hhvm mysqli extension
    //        so we need to reinit before retrying real_connect()
    private function reinit() {
        if (!empty($_ENV['HHVM'])) {
            $this->mysql->init();
            foreach ($this->arrOptions as $optName => $value) {
                $this->mysql->options($optName, $value);
            }
        }
    }

    /**
     * @brief 基于当前连接的字符集escape字符串
     *
     * @param $string 输入字符串
     *
     * @return
     */
    public function escapeString($string) {
        //if enable splitdb
        if(isset($this->splitDB))
        {
            return $this->splitDB->escapeString($string);
        }
        return $this->mysql->real_escape_string($string);
    }

    /**
     * @return Core_Db_SQLAssember|null
     */
    private function __getSQLAssember()
    {
        if($this->sqlAssember == NULL)
        {
            $this->sqlAssember = new Core_Db_SQLAssember($this);
        }
        return $this->sqlAssember;
    }

    /**
     * @brief 设置mysql连接选项
     *
     * @param $optName
     * @param $value
     *
     * @return true：成功；false：失败
     */
    public function setOption($optName, $value) {
        $ret = $this->mysql->options($optName, $value);
        if(!$ret){
            return $ret;
        }
        $this->arrOptions[$optName] = $value;
        return $ret;
    }

    /**
     * @brief 设置连接超时
     *
     * @param $seconds : 超时时间
     *
     * @return
     */
    public function setConnectTimeOut($seconds)
    {
        if($seconds <= 0) {
            return false;
        }
        if (defined('MYSQLI_OPT_CONNECT_TIMEOUT_US')) {
            return $this->setOption(MYSQLI_OPT_CONNECT_TIMEOUT_US, ceil($seconds * 1000000));
        } else {
            return $this->setOption(MYSQLI_OPT_CONNECT_TIMEOUT, ceil($seconds));
        }
    }

    /**
     * @brief 设置读超时
     * @param $seconds : 超时时间
     * @return
     */
    public function setReadTimeOut($seconds)
    {
        if($seconds <= 0) {
            return false;
        }
        if (defined('MYSQLI_OPT_READ_TIMEOUT_US')) {
            return $this->setOption(MYSQLI_OPT_READ_TIMEOUT_US, ceil($seconds * 1000000));
        } else {
            return $this->setOption(MYSQLI_OPT_READ_TIMEOUT, ceil($seconds));
        }
    }

    /**
     * @brief 设置写超时
     * @param $seconds 超时时间
     * @return
     */
    public function setWriteTimeOut($seconds)
    {
        if($seconds <= 0) {
            return false;
        }
        if (defined('MYSQLI_OPT_WRITE_TIMEOUT_US')) {
            return $this->setOption(MYSQLI_OPT_WRITE_TIMEOUT_US, ceil($seconds * 1000000));
        } else {
            return $this->setOption(MYSQLI_OPT_WRITE_TIMEOUT, ceil($seconds));
        }
    }

    public function __destruct() {
        $this->close();
    }

    public function __get($name) {
        switch($name)
        {
            case 'error':
                return $this->mysql->error;
            case 'errno':
                return $this->mysql->errno;
            case 'insertID':
                return $this->mysql->insert_id;
            case 'affectedRows':
                return $this->mysql->affected_rows;
            case 'lastSQL':
                return $this->lastSQL;
            case 'lastCost':
                return $this->lastCost;
            case 'totalCost':
                return $this->totalCost;
            case 'isConnected':
                return $this->isConnected;
            case 'db':
                return $this->mysql;
            default:
                return NULL;
        }
    }
}