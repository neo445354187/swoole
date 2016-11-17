<?php
namespace swoole\log;

/**
 * 日志类，此日志并不是系统错误日志，而是记录用户操作
 * 只能用在Task进程
 *
 */
//表结构,admin_log和log表
// DROP TABLE IF EXISTS `ws_admin_log`;
// CREATE TABLE `ws_admin_log` (
//   `id` int(11) NOT NULL AUTO_INCREMENT,
//   `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
//   `content` varchar(1000) NOT NULL DEFAULT '' COMMENT '用户操作内容',
//   `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '操作内容',
//   PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户日志表';
use swoole\base\Object;
use swoole\base\Progress;

class Logger extends Object
{
    /**
     * [$logs 存储用户日志数组]
     * @var array
     */
    private $logs = array();

    /**
     * [$logs 存储管理员日志数组]
     * @var array
     */
    private $admin_logs = array();

    /**
     * [$max_size 缓存日志最大数量，默认为100条]
     * @var integer
     */
    public $max_size = 100;

    /**
     * 记录等级，现在只是示例，储存字段名用的`type`
     */
    const LEVEL_ERROR = 1;

    const LEVEL_WARNING = 2;

    /**
     * 用户记录表，不用设置表前缀
     * @var string
     */
    public $user_log_table = 'log';

    /**
     * 管理员记录表，不用设置表前缀
     * @var string
     */
    public $admin_log_table = 'admin_log';

    /**
     * 记录日志
     * @param  [type]  $user_id  [用户id，在了解nginx板块后，可以在方法中直接获取user_id，避免赋值操作]
     * @param  [type]  $content  [记录内容]
     * @param  integer $type     [记录类型]
     * @param  boolean $is_admin [是否为管理员操作]
     * @return [type]            []
     */
    public function record($user_id, $content, $type = 1, $is_admin = false)
    {
        if ($is_admin) {
            $this->admin_logs[] = ['user_id' => $user_id, 'content' => $content, 'type' => $type];
            if (count($this->admin_logs) >= $this->max_size) {
                return $this->export(true);
            }
        } else {
            $this->logs[] = ['user_id' => $user_id, 'content' => $content, 'type' => $type];
            if (count($this->logs) >= $this->max_size) {
                return $this->export(false);
            }
        }

    }

    /**
     * 将储存在$this->logs中的记录记录到数据库
     * @param  [type] $is_admin [判断是否是管理员操作]
     * @return [type]           [description]
     */
    public function export($is_admin)
    {
        if ($is_admin ? empty($this->admin_logs) : empty($this->logs)) {
            return true;
        }

        $sql       = "INSERT INTO " . Progress::$app['db']->prefix . ($is_admin ? $this->admin_log_table : $this->user_log_table) . " (user_id,content,type) VALUES (:user_id,:content,:type)";
        $statement = Progress::$app['db']->prepare($sql);
        $logs      = $is_admin ? $this->admin_logs : $this->logs;
        foreach ($logs as $log) {
            $statement->execute($log);
        }
        return $statement->errorCode() === '00000'; #判断是否执行成功
    }

}
