<?php

/**
 * 加载所有功能文件类
 */
class Loader
{
    private static $load_files; #这个加载完后要清空
    /**
     * 在$ws->start()之前加载文件
     * @return [type] [description]
     */
    public static function init()
    {
        // spl_autoload_register([__CLASS__,'autoload'], true, true);
        self::load(require ROOTPATH . '/server/swoole/classMap.php');
        self::$load_files = null;
    }

    /**
     * 在onWorkStart()中work进程加载文件和准备一些工作
     */
    public static function loadWorker()
    {
        self::load(self::getFileList(ROOTPATH . '/worker'));
        self::$load_files = null;
        //准备工作
    }

    /**
     * 在onWorkStart()中task进程加载文件和准备一些工作
     */
    public static function loadTask()
    {
        self::load(self::getFileList(ROOTPATH . '/task'));
        self::$load_files = null;
        //准备工作
    }

    /**
     * 加载定时任务文件
     */
    public static function loadTimer()
    {
        self::load(self::getFileList(ROOTPATH . '/timer'));
        self::$load_files = null;
        //准备工作
    }

    /**
     * 加载目录中的所有文件
     * @param  string $files 需要加载文件的数组
     */
    private static function load($files)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                require $file;
            }
        }
    }

    /**
     * 自动加载非核心文件
     * @param  [type] $className [description]
     * @return [type]            [description]
     */
    /*private static function autoload($className)
    {
        $file = ROOTPATH . '/' . strtr($className, '\\', '/') . '.php';
        if (is_file($file)) {
            require $file;
        } else {
            throw new \Exception('error:' . $file . ' is not a file. please check directory!');
        }

    }*/

    /**
     * 递归获取所有文件
     * @param  string $dir 文件夹
     * @return $result array 返回一维数组
     */
    private static function getFileList($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $arr = scandir($dir);
        foreach ($arr as $value) {
            if (in_array($value, ['.', '..'])) {
                continue;
            }

            $d = $dir . DIRECTORY_SEPARATOR . $value;
            if (is_dir($d)) {
                self::getFileList($d);
            } else {
                self::$load_files[] = $d;
            }
        }
        return self::$load_files;
    }

    /**
     * 合并配置文件数组
     * @param  [type] $a [description]
     * @param  [type] $b [description]
     * @return [type]    [description]
     */
    public function merge($a, $b)
    {
        $args = func_get_args();
        $res  = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_int($k)) {
                    //即元素下标是数字的，是不会覆盖的，但是是递归的出口之一；
                    if (isset($res[$k])) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    //两个合并数组的下标都是字符串，且对应值都是数组时，则递归；
                    $res[$k] = self::merge($res[$k], $v);
                } else {
                    $res[$k] = $v; //除以上情况，直接覆盖，递归出口之一；
                }
            }
        }

        return $res;
    }

}

//执行加载类初始化
\Loader::init();
