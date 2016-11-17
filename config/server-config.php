<?php
//服务器参数
return [
    //服务器监听地址
    'addr'   => [
        'ip'   => '0.0.0.0',
        'port' => 9502,
    ],
    //服务器配置参数
    'config' => [
        'reactor_num'        => 8, //设置为cpu的1-4倍，小于或者等于worker_num数量

        'worker_num'         => 8, //设置为cpu的1-4倍

        'task_worker_num'    => 8, //务必要注册onTask/onFinish2个事件回调函数。如果没有注册，服务器程序将无法启动。计算方法：http://wiki.swoole.com/wiki/page/276.html；

        'task_ipc_mode'      => 3, //worker进程与task进程通信方式设置为争抢模式，高手说模式3，消息队列模式使用操作系统提供的内存队列存储数据，数据大小没有限制

        //'pipe_buffer_size' => 32 * 1024 *1024, //必须为数字，调整管道通信的内存缓存区长度。Swoole使用Unix Socket实现进程间通信。task_ipc_mode为2或3，此配置无效

        'task_max_request'   => 5000, //task_max_request默认为5000，受swoole_config.h的SW_MAX_REQUEST宏控制1.7.17以上版本默认值调整为0，不会主动退出进程

        'backlog'            => 128, //参数将决定最多同时有多少个等待accept的连接

        'dispatch_mode'      => 2, //主进程向worker进程分发数据包方式，最好别改

        // 'daemonize' => 1,        //开启守护进程模式后(daemonize => true)，标准输出将会被重定向到log_file。在PHP代码中echo/var_dump/print等打印到屏幕的内容会写入到log_file文件

        'log_file' => ROOTPATH.'/log/swoole.log', //指定swoole错误日志文件
        
        'log_level' => 1,//设置swoole_server错误日志打印的等级，范围是0-5。低于log_level设置的日志信息不会抛出。

        'package_max_length' => 81920, //设置最大数据包尺寸

        'user'               => 'root', //设置进程所有者，root启动有效,能让程序正确运行的前提下, 权限越低越好.

        'group'              => 'root', //设置进程所属组，提高安全性

        'enable_reuse_port'  => true, // 打开端口重用,设置端口重用，此参数用于优化TCP连接的Accept性能，启用端口重用后多个进程可以同时进行Accept操作。

        'heartbeat_check_interval' => 60,//

        'heartbeat_idle_time' => 3600,//
    ],
];
