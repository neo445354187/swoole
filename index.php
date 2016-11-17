<?php
/**
 *文件说明：服务器主文件
 *开启服务命令：/usr/local/php/bin/php ws_server.php
 *
 */
//设置根目录
define('ROOTPATH', __DIR__);

//引入加载文件
require ROOTPATH . '/server/Loader.php';

//运行整个应用
(new swoole\App(require ROOTPATH . '/config/server-config.php'))->run();



