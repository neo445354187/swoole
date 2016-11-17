<?php
/**
 * 本文件配置针对worker和task进程，不能用于服务器配置
 */
return [
    'components' => [ //放在组件的$_components属性中，获取某个组件时，自动调用__get()，再通过响应方法从定义中获取配置创建实例
        //缓存
        'cache'       => [
            'class' => 'swoole\caching\Cache',
            'prefix' => 'ws_',          #表前缀
            'config'=>[
                'host' => '127.0.0.1',
                'port' => 6379,
                'password' => '121212',
            ],
        ],
        //验证
        'validator'       => [
            'class' => 'swoole\validate\Validator',
            
        ],
        //安全
        'security'       => [
            'class' => 'swoole\secure\Security',
        ],

    ],
    'property' => [
    	'language'   => 'zh-CN', //设置简体中文,如果采用其他语言，则使用Yii::$App->language = 'en';

    	'timeZone'   => 'Asia/Chongqing', //设置时区
    ],
    

];
