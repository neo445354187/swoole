<?php
namespace swoole;

/**
 * 文件说：实现服务器监听事件后的回调函数；
 */
use Loader;
use swoole\base\Progress;
use swoole\Task;
use swoole\Timer;
use swoole\Worker;

class App
{
    public $config = array(); #存储整个应用默认配置，暂时全部采用配置文件的，无默认

    public function __construct($server_config = array())
    {
        $this->config = array_merge($this->config, $server_config);

    }

    /**
     * 运行整个应用
     * @return [type] [description]
     */
    public function run()
    {
        //创建websocket服务器对象，监听0.0.0.0:9502端口
        $ws = new \swoole_websocket_server($this->config['addr']['ip'], $this->config['addr']['port']);

        //设置参数
        $ws->set($this->config['config']);

        //开启worker和task进程回调
        $ws->on('Start', array(__CLASS__, 'start'));

        //开启worker和task进程回调
        $ws->on('Shutdown', array(__CLASS__, 'shutdown'));

        //开启worker和task进程回调
        $ws->on('ManagerStart', array(__CLASS__, 'managerStart'));

        //关闭管理进程回调
        $ws->on('ManagerStop', array(__CLASS__, 'managerStop'));

        //开启worker和task进程回调
        $ws->on('WorkerStart', array(__CLASS__, 'workerStart'));

        $ws->on('WorkerStop', array(__CLASS__, 'workerStop'));

        $ws->on('WorkerError', array(__CLASS__, 'workerError'));

        //监听WebSocket连接打开事件
        $ws->on('Open', array(__CLASS__, 'open'));

        //监听WebSocket消息事件
        $ws->on('Message', array(__CLASS__, 'message'));

        //监听WebSocket连接关闭事件
        $ws->on('Close', array(__CLASS__, 'close'));

        //功能：投递任务
        $ws->on('Task', array(__CLASS__, 'task'));

        //功能：处理异步任务返回结果，如果有后续操作，则执行后续操作；
        $ws->on('Finish', array(__CLASS__, 'finish'));

        //设置定时器触发回调
        // $ws->on('Timer', array('\swoole\App', 'timer'));

        //开启一个process进程来专门管理定时任务
        Timer::$process = new \swoole_process(function ($process) use ($ws) {
            $process->name("swoole process"); #修改进程名称
            Progress::$server = $ws;
            Loader::loadTimer();
            Timer::init(Loader::merge(require ROOTPATH . '/config/common-config.php', require ROOTPATH . '/config/timer-config.php'));
        });
        // Timer::$process->useQueue();
        $ws->addProcess(Timer::$process);

        $ws->start();
    }

    /**
     * Server启动在主进程的主线程回调此函数
     * 说明：
     * 在此事件之前Swoole Server已进行了如下操作
     *
     * 已创建了manager进程
     * 已创建了worker子进程（在onStart中创建的全局资源对象不能在worker进程中被使用，因为发生onStart调用时，worker进程已经创建好了。）
     * 已监听所有TCP/UDP端口
     * 已监听了定时器
     *
     * 接下来要执行
     *
     * 主Reactor开始接收事件，客户端可以connect到Server
     *
     * 说明2：可以在onStart中执行的操作：仅允许echo、打印Log、修改进程名称。
     * 以及将$serv->master_pid和$serv->manager_pid的值保存到一个文件中。这样可以编写脚本，向这两个PID发送信号来实现关闭和重启的操作。
     *
     *
     * @param  object $ws [服务器对象]
     * @return [type]     [description]
     */
    public static function start($ws)
    {
        swoole_set_process_name('swoole master');
    }

    /**
     * 此事件在Server结束时发生
     * 说明：
     * 在此之前Swoole Server已进行了如下操作
     *
     * 已关闭所有线程
     * 已关闭所有worker进程
     * 已close所有TCP/UDP监听端口
     * 已关闭主Rector
     * 强制kill进程不会回调onShutdown，如kill -9
     * 需要使用kill -15来发送SIGTREM信号到主进程才能按照正常的流程终止
     *
     * @param  object $ws [服务器对象]
     * @return [type]     [description]
     */
    public static function shutdown($ws)
    {
        # code...
    }

    /**
     * 当管理进程启动时调用它，一般在这里重命名管理进程
     * @param  [type] $ws [服务器对象]
     * @return [type]     [description]
     */
    public function managerStart($ws)
    {
        swoole_set_process_name('swoole manager');
    }

    /**
     * 当管理进程启动时调用它，一般在这里重命名管理进程
     * @param  [type] $ws [服务器对象]
     * @return [type]     [description]
     */
    public function managerStop($ws)
    {
        # code...
    }

    /**
     * 功能：workerStart回调
     * worker进程开启时，引入控制器和同步操作文件
     * task进程开启时，引入异步操作文件
     */
    public static function workerStart($ws, $worker_id)
    {
        Progress::$server = $ws;
        if ($ws->taskworker) {
            swoole_set_process_name('swoole tasker ' . $worker_id);
            // task进程
            Loader::loadTask();
            Task::init(Loader::merge(require ROOTPATH . '/config/common-config.php', require ROOTPATH . '/config/task-config.php'));

        } else {
            swoole_set_process_name('swoole worker ' . $worker_id);
            //worker进程
            Loader::loadWorker();
            Worker::init(Loader::merge(require ROOTPATH . '/config/common-config.php', require ROOTPATH . '/config/worker-config.php'));

        }

    }

    /**
     * 功能：workerStop回调
     * 此事件在worker进程终止时发生。在此函数中可以回收worker进程申请的各类资源。
     * task进程开启时，引入异步操作文件
     */
    public static function workerStop($ws, $worker_id)
    {
        if ($ws->taskworker) {
            //task进程，回收数据库连接

            //将日志记录到数据库
            Task::$app['logger']->export(false);
            Task::$app['logger']->export(true);
        } else {
            //worker进程

        }
        Progress::$server = Progress::$config = Progress::$app = null;
    }

    /**
     * 当worker/task_worker进程发生异常后会在Manager进程内回调此函数。
     * @param  object $ws          服务器对象
     * @param  int    $worker_id  异常进程的编号
     * @param  int    $worker_pid 异常进程的ID
     * @param  int    $exit_code  $exit_code退出的状态码，范围是 1 ～255
     * @return [type]             [description]
     */
    public function workerError($ws, $worker_id, $worker_pid, $exit_code)
    {
        # 主要用于报警和监控，一旦发现Worker进程异常退出，那么很有可能是遇到了致命错误或者进程CoreDump。
        # 通过记录日志或者发送报警的信息来提示开发者进行相应的处理。
    }

    /**功能：连接socket成功的初始化操作
     * @param  $ws     object    服务器对象
     * @param  $req 是一个swoole_http_request请求对象，验证用户权限数据，放在websocket的url
     * 中，这里通过$request->get得到
     * 说明：这里就是会话期开始了，高手：一个连接一个会话没错，但是变量并不是彼此之间独立的…
     * 又不是fpm每个连接一个进程……，
     *
     */
    public static function open($ws, $request)
    {
        Worker::$request = $request;
        //进行CSRF防御
        // if (!Worker::$app['security']->defendCSRF($request->get['ws_token'])) {
        //     $ws->close($request->fd);
        // }

        //释放Worker::$request
        worker::$request = null;
    }

    /**
     * 功能：执行操作
     * @param  $ws     object    服务器对象
     * @param  $frame          共有4个属性，分别是
     *
     *    $frame->fd，客户端的socket id，使用$server->push推送数据时需要用到
     *    $frame->data，数据内容，可以是文本内容也可以是二进制数据，可以通过opcode的值来判断
     *        数据说明：
     *            $frame->data['handle']            具体操作
     *            $frame->data['auth_token']      既用于nginx板块，又用于swoole板块的token
     *            $frame->data['data']              提交数据
     *    $frame->opcode，WebSocket的OpCode类型，可以参考WebSocket协议标准文档
     *    $frame->finish， 表示数据帧是否完整，一个WebSocket请求可能会分成多个数据帧进行发送
     */
    public static function message($ws, $frame)
    {

        //路由
        $frame->data   = json_decode($frame->data, true);
        $handle        = explode('/', $frame->data['handle']);
        $handle_class  = '\\worker' . '\\' . $handle[0] . '\\' . $handle[1];
        $handle_action = $handle[2];
        //检查操作是否存在
        // if (!method_exists($handle_class, $handle_action)) {
        //     $info = [
        //         'handle' => 'alter',
        //         'data'   => 'error : the action is not found!',
        //     ];
        //     $ws->push($frame->fd, json_encode($info)); #push并不会结束后面代码执行
        // } else {
        //     //判断用户操作权限
        //     if (!empty($frame->data['auth_token']) && Worker::$app['auth']->checkAuth($frame->data['auth_token'], $handle_class, $handle_action)) {
        $frame->data   = $frame->data['data'];
        Worker::$frame = $frame;
        call_user_func(array($handle_class, $handle_action));

        //消除请求期数据，高手：可以释放，仅限于当前进程
        Worker::$frame  = null;
        Progress::$data = null;
        // } else {
        //     $info = [
        //         'handle' => 'alter',
        //         'data'   => 'error : You have no rights!',
        //     ];
        //     $ws->push($frame->fd, json_encode($info));
        // }
        // }
    }

    /**功能：断开socket成功的初始化操作
     * @param     $server是swoole_server对象
     * @param    $fd是连接的文件描述符
     * @param    $from_id来自那个reactor线程
     */
    public static function close($ws, $fd, $from_id)
    {
        // \front\worker\Chat::offLine($ws, $fd); //可以直接调用任务
        // echo "client-{$fd} is closed\n";
    }

    /**功能：投递一个异步任务的回调
     * @param   $data   array   包含下标为'handle'处理操作、下标为'afterHandle'元素(表示回调操作)，
     *                          以及下标'data'真正要处理的数据
     */
    public static function task($ws, $task_id, $from_id, $data)
    {
        $handle         = explode('::', $data['handle']);
        $result['data'] = call_user_func(array($handle[0], $handle[1]), $data['data']);
        //销毁Task进程中全局变量
        $handle[0]::$data = null;
        if ($data['callback'] !== '') {
            $result['callback'] = $data['callback'];
            $result['fd']       = $data['fd'];
            return json_encode($result);
        }

    }

    /**
     * 功能：投递一个异步任务完成的回调
     * 说明：回调执行函数参数格式为($ws, $fd, $data)
     * 再次考虑一下采用静态类和静态方法，不用实例化，效率高些；
     */
    public static function finish($ws, $task_id, $data)
    {
        $data   = json_decode($data, true);
        $handle = explode('::', $data['callback']);
        call_user_func(array($handle[0], $handle[1]), $data['fd'], $data['data']);
        //销毁Worker进程中全局变量
        $handle[0]::$data = null;
    }

}
