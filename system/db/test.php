<?php


class Bd_DB
{

    private $mysql = NULL;
    private $isConnected = false;

    public function __construct($enableProfiling = false)
    {
        $this->mysql = mysqli_init();
    }

    /**
     * @brief 连接方法
     *
     * @param $host 主机
     * @param $uname 用户名
     * @param $passwd 密码
     * @param $dbname 数据库名
     * @param $port 端口
     * @param $flags 连接选项
     *
     * @return true：成功；false：失败
     */
    public function connect($host, $uname = null, $passwd = null, $dbname = null, $port = null,
                            $flags = 0, $retry = 0, $service = '')
    {

        for ($i=0; $i <= $this->retrytimes; $i++) {
            $this->isConnected = $this->mysql->real_connect(
                $host, $uname, $passwd, $dbname, $port, NULL, $flags
            );
        }

        return $this->isConnected;
    }
}
