<?php
namespace swoole\caching;

/**
 * 数据库类
 */
use Exception;
use Redis;
use swoole\base\Object;

class Cache extends Object
{
    public $prefix; //前缀

    public $redis;

    //配置参数
    public $config = [
        'host'     => '127.0.0.1', 
        'port'     => 6379,
        'password' => '121212',
        'prefix'   => 'swoole_',

    ];


    /**
     * 初始化
     * @return [type] [description]
     */
    public function init()
    {
        try {
            $this->redis = new Redis();
            $this->redis->connect($this->config['host'], $this->config['port']);
            $this->redis->auth($this->config['password']);
        } catch (Exception $e) {
            //改成记录到日志文件
            echo "error：\n" . 'host: ' . $this->config['host'] . ' port: ' . $this->config['port'] . ' password: ' . $this->config['password'] . $e->getMessage();
        }
    }

    /**
     * 封装pdo操作到DB类中，现在DB类对象可以像PDO类对象一样使用了；
     * @param  [type] $method [description]
     * @param  [type] $args   [description]
     * @return [type]         [description]
     */
    public function __call($method, $args)
    {
        //断开重连
        try {
            return call_user_func_array(array($this->redis, $method), $args);
        } catch (Exception $e) {
            $this->init();
            return call_user_func_array(array($this->redis, $method), $args);
        }
        
    }

}
