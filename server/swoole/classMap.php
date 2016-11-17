<?php
//返回加载文件映射，需要加载所有server文件，而且不能用自动加载，
//因为要么有的文件没加载，要么有的加载重复冲突
//文件顺序不要改变
return [
    ROOTPATH.'/server/swoole/base/Object.php',
    ROOTPATH.'/server/swoole/base/Progress.php',
    ROOTPATH.'/server/swoole/Worker.php',
    ROOTPATH.'/server/swoole/Task.php',
    ROOTPATH.'/server/swoole/Timer.php',
    ROOTPATH.'/server/swoole/App.php',
    
    //加载组件
    ROOTPATH.'/server/swoole/caching/Cache.php',
    ROOTPATH.'/server/swoole/db/Connection.php',
    ROOTPATH.'/server/swoole/log/Logger.php',
    ROOTPATH.'/server/swoole/rbac/Auth.php',
    ROOTPATH.'/server/swoole/secure/Security.php',
    ROOTPATH.'/server/swoole/validate/Validator.php',

];
