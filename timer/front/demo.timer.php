<?php
namespace timer\front;

use swoole\Timer;

/**
 *
 */
class Demo extends Timer
{

    /**
     * 向所有连接的客户端发送消息
     * @return [type] [description]
     */
    public static function sendTickTimer()
    {
        $data               = array();
        Timer::$data['one'] = Timer::$server->tick(5000, function () {
            foreach (Timer::$server->connections as $fd) {
                Timer::$server->push($fd, "this is from tick " . rand());
            }
        }, $data); #tick()的第三个参数可以参数数据
    }

    /**
     * [TestProcessTimer 配置]
     */
    public static function testProcessTimer()
    {
        // echo "testProcessTimer running" . PHP_EOL;
        // Timer::$server->tick(2000, function () {
        swoole_timer_tick(2000, function () {
            $process = new \swoole_process(function ($worker) {
                Timer::initWorker();
                // $worker->name('swoole-process');
                echo "new worker start!" . PHP_EOL;
                // var_dump(Timer::$server);
                sleep(1);
                var_dump(Timer::$app['db']->table('__USER__')->field('name,email')->where('id = :id')->fetch(['id' => 1]));
                echo "new worker shutdown!" . PHP_EOL;
                $worker->exit(1);
            });
            $pid = $process->start();
            \swoole_process::wait();
        });
    }

    public static function testProcessTwoTimer()
    {
        echo "testProcessTimerTwo running" . PHP_EOL;
        Timer::$server->tick(1000, function () {
            $process = new \swoole_process(function ($worker) {
                Timer::initWorker();
                echo "new worker two start!" . PHP_EOL;
                var_dump(Timer::$app['db']->table('__USER__')->field('name,email')->where('id = :id')->fetch(['id' => 2]));
                // $worker->name('new process start');
                sleep(1);
                echo "new worker two shutdown!" . PHP_EOL;
                $worker->exit(1);
            });
            $pid = $process->start();

            \swoole_process::wait();
        });
    }

    //测试返回timer_id
    // public static function TestTickTimer()
    // {
    //     $data = array();
    //     Timer::$data['two'] = Timer::$server->tick(5000, function () {
    //         foreach (Timer::$server->connections as $fd) {
    //             Timer::$server->push($fd, "this is from test tick " . rand());
    //         }
    //     },$data);#tick()的第三个参数可以参数数据
    // }

    // public static function TestclearTimer()
    // {
    //     Timer::$server->after(10000, function () {
    //         Timer::$server->clearTimer(Timer::$data);
    //     });
    // }

    /**
     * 向所有连接的客户端发送消息
     * @return [type] [description]
     */
    public static function sendAfterTimer()
    {
        // var_dump(Timer::$app);
        //注意：定时器是针对服务器定时，并不是针对连接客户端
        Timer::$server->after(15000, function () {
            foreach (Timer::$server->connections as $fd) {
                Timer::$server->push($fd, "this is from after " . rand());
            }
        });
    }

    /**
     * 测试记录日志
     * @return [type] [description]
     */
    public static function recordTimer()
    {
        //注意：定时器是针对服务器定时，并不是针对连接客户端
        Timer::$server->tick(5000, function () {
            // Timer::$app['logger']->record(1, "this is test log!", 1,true);
        });
    }
}
