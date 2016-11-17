<?php
namespace worker\front;

use swoole\Worker;

/**
 * 文件说明：worker进程功能测试文件
 * 说明：
 * 1）虽然把一些操作封装在类中，但由于是异步的，类的静态变量在同步阻塞时共享，异步非阻塞时不共享；
 * 2）所有worker文件夹中的操作方法前两个参数必须是$ws,$frame。如果是直接能被前端路由的操作，就只有
 * 这两个形参，如果是异步回调函数，则第三个参数是异步任务返回的结果。
 * 3）如果需要执行异步任务，那么必须设置回调操作；
 * 4）在前端能直接访问的操作中一定要严格判断数据是否是自己需要的，以防止攻击，当然也可以利用Security
 * 类进行判断
 */
class Demo extends Worker
{

    /**
     * 说明：所有前端能指定的worker操作，必须是这两个参数，注意这儿的$frame->data已经是纯数据了
     * 不再包含路由；
     */
    public static function sendMsg()
    {
        echo "Message: " . Worker::$frame->data . PHP_EOL;
        Worker::$server->push(Worker::$frame->fd, "my socket id: " . Worker::$frame->fd . "，server: " . Worker::$frame->data);
    }

    /**
     * 前台访问方法
     * @return [type] [description]
     */
    public static function getUserInfo()
    {
        Worker::createTask('task\front\User::getUser', 'worker\front\demo::_getUserInfo', Worker::$frame->fd);
    }

    /**
     * 执行完task回调方法
     * @param  [type] $fd   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function _getUserInfo($fd, $data)
    {
        Worker::$server->push($fd, "my fd is {$fd} ,my name is {$data['name']} ,and my email is {$data['email']}");
    }

    /**
     * redis缓存演示
     */
    public static function setCache()
    {
        // var_dump(Worker::$app['cache']);
        Worker::$app['cache']->set('name', 'chao');
    }

    public static function getCache()
    {
        $name = Worker::$app['cache']->get('name');
        Worker::$server->push(Worker::$frame->fd, $name);
    }

    /**
     * 数据验证演示
     * @return [type] [description]
     */
    public function validate()
    {
        $msg = "validated result: ";
        if (Worker::$app['validator']->validateEmail(Worker::$frame->data['email'])) {
            $msg .= "email is right;";
        } else {
            $msg .= "email is wrong;";
        }
        if (Worker::$app['validator']->validateUrl(Worker::$frame->data['url'])) {
            $msg .= "url is right;";
        } else {
            $msg .= "url is wrong;";
        }
        if (Worker::$app['validator']->validateMobile(Worker::$frame->data['mobile'])) {
            $msg .= "mobile is right;";
        } else {
            $msg .= "mobile is wrong;";
        }

        Worker::$server->push(Worker::$frame->fd, $msg);
    }

}
