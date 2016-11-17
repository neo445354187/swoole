<?php
return [
    'components' => [ //放在组件的$_components属性中，获取某个组件时，自动调用__get()，再通过响应方法从定义中获取配置创建实例
        'db'     => [
            'class'  => 'swoole\db\Connection',
            'prefix' => 'ws_', #表前缀
            'config' => [
                'dsn'            => 'mysql:dbname=swoole;host=127.0.0.1', 
                'user'           => 'root',
                'password'       => '121212',
                'driver_options' => [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_PERSISTENT         => true,
                ],
            ],
        ],
        'logger' => [
            'class' => 'swoole\log\Logger',
        ],
    ],
    'property'   => [

    ],

];
