<?php

class Core_Db_Db {

    const FETCH_RAW = 0;    // return raw mysqli_result
    const FETCH_ROW = 1;    // return numeric array
    const FETCH_ASSOC = 2;  // return associate array
    const FETCH_OBJ = 3;    // return Bd_DBResult object

    public $mysql = NULL;
    /**
     * @var Core_Db_SQLAssembler
     */
    private $sqlAssembler = NULL;
    private $lastSQL = "";
    private $totalCost = 0;
    private $lastCost = 0;
    private $arrOptions = array();

    public $isConnected = false;
    public $dbConfig = array();
    public $retryTime = 0;

    private static $_instance;

    /**
     * @param $dbName
     * @return Core_Db_Db
     */
    public static function getInstance($clusterName) {
        if(!self::$_instance[$clusterName]) {
            self::$_instance[$clusterName] = new self($clusterName);
        }
        return self::$_instance[$clusterName];
    }
    /**
     * 构造函数
     */
    private function __construct($clusterName) {
        $this->mysql = mysqli_init();
        $this->__getSQLAssembler();
        $dbConfig = Core_Config::getInstance("database")->item($clusterName);
        $host = $dbConfig["hostname"];
        $userName = $dbConfig["username"];
        $password = $dbConfig["password"];
        $dbName = $dbConfig["database"];
        $port = $dbConfig["port"];
        if(!$this->connect($host, $userName, $password, $dbName, $port, 3)) {
            throw new Core_Exception(Core_Exception::SYS_DB_CONNECT_FAIL);
        }
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
     * @throws Core_Exception
     */
    public function select($table,
                           $fields,
                           $condition,
                           $option = null,
                           $append = array(),
                           $fetchType = Core_Db_Db::FETCH_ASSOC) {
        $sql = $this->sqlAssembler->getSelect($table, $fields, $condition, $option, $append);
        if(!$sql) {
            return false;
        }
        return $this->query($sql, $fetchType);
    }

    public function selectCount($table, $condition = null, $option = null, $append = null) {
        $fields = "COUNT(*)";
        $sql = $this->sqlAssembler->getSelect($table, $fields, $condition, $option, $append);
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
        $sql = $this->sqlAssembler->getInsert($table, $row, $option, $onDup);
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
        $sql = $this->sqlAssembler->getUpdate($table, $row, $condition, $option, $append);
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
        $sql = $this->sqlAssembler->getDelete($table, $condition, $option, $append);
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
        $this->lastSQL = $sql;
        $beg = intval(microtime(true)*1000000);
        $res = $this->mysql->query($sql);
        $this->lastCost = intval(microtime(true)*1000000) - $beg;
        $this->totalCost += $this->lastCost;
        if(is_bool($res) || $res === null) {
            $ret = ($res == true);
            if(!$ret) {
                //log
                throw new Core_Exception(Core_Exception::SYS_DB_CONNECT_FAIL, $sql);
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
        if(isset($this->splitDB))
        {
            return $this->splitDB->escapeString($string);
        }
        return $this->mysql->real_escape_string($string);
    }

    /**
     * @return Core_Db_SQLAssembler|null
     */
    private function __getSQLAssembler()
    {
        if($this->sqlAssembler == NULL)
        {
            $this->sqlAssembler = new Core_Db_SQLAssembler($this);
        }
        return $this->sqlAssembler;
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

    /**
     * @brief 获取上一次SQL语句
     *
     * @return
     */
    public function getLastSQL() {
        return $this->lastSQL;
    }

    /**
     * @brief 获取当前mysqli错误描述
     *
     * @return
     */
    public function error() {
        return $this->mysql->error;
    }
}