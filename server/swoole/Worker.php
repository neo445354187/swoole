<?php
namespace swoole;
/**
 * Worker进程串联器，进程初始化和全局作用
 */
use swoole\base\Progress;
class Worker extends Progress
{
    public static $request;   #存储onOpen回调中的$request，注意在onMessage中结尾处将其删除

    public static $frame;     #存储$frame，注意在onMessage中结尾处将其删除

    /**
     * 进行Worker进程的初始化工作
     * @param  array $config 配置数组
     * @return [type]         
     */
    public static function init($config = array())
    {
        //存儲配置
        Progress::$config = $config;
        unset($config);
        //将所有配置初始化为对象，并放在该类的$App属性上；
        foreach ( Progress::$config['components'] as $key => $value) {
            Progress::$app[$key] = Progress::createObject($value);
        }

    }

    /**
     * 创建异步投递任务
     * @param  object     $ws               websocket资源对象
     * @param  int        $fd               swoole_websocket_frame对象的fd属性，即文件描述符
     * 
     * @param  string     $handle           处理异步任务操作，例：task\front\user::login
     * @param  string     $callback         处理异步任务完成后的回调操作，例：worker\front\user::getName
     * @param  mixed      $data             投递给处理操作的数据
     * @param  integer    $dst_worker_id    要给投递给哪个task进程，传入ID即可，范围是0 - (serv->task_worker_num -1)，默认为随机
     */
    public static function createTask($handle, $callback = '', $data = '', $dst_worker_id = -1)
    {
        $task_info = [
            'fd'       => self::$frame->fd,
            'handle'   => $handle,
            'callback' => $callback,
            'data'     => $data,
        ];
        //尝试换成swoole_server_task，省掉$ws
        Progress::$server->task($task_info, $dst_worker_id);
    }
}
