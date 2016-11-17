<?php
namespace swoole;
/**
 * Task进程初始化和全局作用
 */
use swoole\base\Progress;
class Task extends Progress
{

    /**
     * 进行Task进程的初始化工作
     * @param  array $config 配置数组
     * @return [type]         [description]
     */
    public static function init($config = array())
    {
        //存儲配置
        Progress::$config = $config;
        unset($config);
        //将所有配置初始化为对象，并放在该类的$App属性上；
        foreach (Progress::$config['components'] as $key => $value) {
            Progress::$app[$key] = Progress::createObject($value);
        }
    }


}
