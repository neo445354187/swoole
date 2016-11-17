<?php
return [
    'components' => [ //放在组件的$_components属性中，获取某个组件时，自动调用__get()，再通过响应方法从定义中获取配置创建实例
        // 'cache'       => [
        //     'class' => 'yii\caching\FileCache',
        // ],
        // 'authManager' => [
        //     'class' => 'yii\rbac\DbManager',
        // ],
        // 'i18n'        => [
        //     'translations' => [
        //         'App*' => [     //如果需要多个语言文件，必须含有通配符
        //         'class' => 'yii\i18n\PhpMessageSource',
        //         'basePath' => '@App/messages', //前后台都通用
        //         // 'sourceLanguage' => 'en-US',//默认就行
        //         'fileMap' => [
        //         'App/te' => 'te.php', //键名必须和符合上面的统配规则，即类似于上面的正则可以匹配该键名，详情查看类I18N的getMessageSource()方法

        //         ],
        //         ],
        //         '*' => [ //如果需要多个语言文件，必须含有通配符
        //             'class'    => 'yii\i18n\PhpMessageSource',
        //             'basePath' => '@App/messages', //前后台都通用

        //         ],
        //     ],
        // ],
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
