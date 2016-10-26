<?php

/**
 * Class Market_Redis
 * Redis缓存管理器,将redis专门作为缓存使用时，请调用该类的方法
 */
class Library_Redis {

    /**
     * @return mixed|\Redis
     */
    public static function getConnection() {
        if ( $instance = Ap_Registry::get('REDIS_INSTANCE') ){
            return $instance;
        }

        $config = Bd_Conf::getConf('/client_' . AP_ENVIRON . '/redis');
        $redis  = new \Redis();
        $passwd = $config['password'];
        foreach ($config['host'] as $host) {
            if ($redis->connect($host['ip'], (int)$host['port'])) {
                $redis->auth($passwd);
                break;
            }
        }

        //$redis->select(5);
        Ap_Registry::set('REDIS_INSTANCE', $redis);
        return $redis;
    }
}