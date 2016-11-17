<?php

/**
 * 错误接管类
 */
class ErrorHandler 
{
    //调试等级，在非测试环境起作用
    protected $levels = [
        E_ERROR,
        E_WARNING,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
    ];

    public function register()
    {
        //禁止错误输出
        ini_set("display_errors", "off");
        //设置异常处理器
        set_exception_handler([$this, 'exceptionHandler']);
        //设置错误处理器
        set_error_handler([$this, 'errorHandler']);
        //设置致命错误处理器
        register_shutdown_function([$this, 'fatalErrorHandler']);
    }
    /**
     * 异常处理器
     * @param  object $error 异常类Exception对象
     * @return [type]    [description]
     */
    public function exceptionHandler($error)
    {

        $this->errorHandler($error);
    }
    /**
     * 错误处理器
     * @param  [type] $errno   错误代码
     * @param  [type] $errstr  错误消息
     * @param  [type] $errfile 错误文件
     * @param  [type] $errline 错误行号
     * @return [type]          [description]
     */
    public function errorHandler($error)
    {

        //判断是否是设置的等级，是则记录
        if (in_array($errno, $this->levels)) {
            $message = $error['message'];
            $file    = $error['file'];
            $line    = $error['line'];
            $log     = '[' . date('Y-m-d h:i:s') . '] ' . "$message ($file:$line)\nStack trace:\n";
            $trace   = debug_backtrace();
            foreach ($trace as $i => $t) {
                if (!isset($t['file'])) {
                    $t['file'] = 'unknown';
                }
                if (!isset($t['line'])) {
                    $t['line'] = 0;
                }
                if (!isset($t['function'])) {
                    $t['function'] = 'unknown';
                }
                $log .= "#$i {$t['file']}({$t['line']}): ";
                if (isset($t['object']) and is_object($t['object'])) {
                    $log .= get_class($t['object']) . '->';
                }
                $log .= "{$t['function']}()\n";
            }

            $log_dir = ROOTPATH . '/log';
            if (!file_exists($log_dir)) {
                mkdir($log_dir, 755);
            }
            file_put_contents($log_dir . '/log.txt', $log, FILE_APPEND | LOCK_EX);
        }
        die;
    }

    /**
     * 致命错误处理器
     * @return [type] [description]
     */
    public function fatalErrorHandler()
    {

        $error = error_get_last();
        switch ($error['type']) {
            case E_ERROR :
            case E_WARNING:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $this->errorHandler($error);
                break;
            default:
                break;
        }
    }
}
