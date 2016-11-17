<?php
namespace swoole;

/**
 * Timer全局
 */
use swoole\base\Progress;

class Timer extends Progress
{

    /**
     * [$process 放置主进程]
     * @var [type]
     */
    public static $process;

    /**
     * 进行Process主进程的初始化工作
     * @param  array $config 配置数组
     * @return [type]         [description]
     */
    public static function init($config = array())
    {
        //存儲配置
        Progress::$config = $config;
        unset($config);

        //获取已经声明的所有类
        $classes = get_declared_classes();
        foreach ($classes as $class) {
            //判断是否是定时类
            if (strpos($class, 'timer\\') === 0) {
                //获取定时类的所有方法
                $methods = get_class_methods($class);
                foreach ($methods as $method) {
                    if (substr($method, -5) === 'Timer') {
                        call_user_func(array($class, $method));
                    }
                }

            }
        }
    }

    /**
     * 进行Process子进程的初始化工作，主要是重新建立连接
     * @param  array $config 配置数组
     * @return [type]         [description]
     */
    public static function initWorker()
    {
        //将所有配置初始化为对象，并放在该类的$App属性上；
        foreach (Progress::$config['components'] as $key => $value) {
            Progress::$app[$key] = Progress::createObject($value);
        }

    }

}
