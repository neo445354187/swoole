<?php
namespace swoole\secure;

/**
 * 安全类
 */
use swoole\base\Object;
use swoole\base\Progress;

class Security extends Object
{
    /**
     * [$csrf_salt 用于CSRF的token验证，这个要与nginx里的设置redis中标记的代码这个部分相同才行]
     * @var string
     */
    private $csrf_salt = "{zcyx9wl@";

    /**
     * 进行XSS防御
     * @param  [string] $value [要进行实体化的字符串]
     * @return [string]        返回已经实体化的字段
     */
    public function defendXSS($value)
    {
        return htmlspecialchars($value);
    }

    /**
     * 进行CSRF防御，这里的$value，即页面的ws_token 只用一次，用完即删
     * @param  [string] $value [要进行实体化的字符串]
     * @return [string]        返回已经实体化的字段
     */
    public function defendCSRF($value)
    {
        if ($result = Progress::$app['cache']->get($value)) {
            return Progress::$app['cache']->del($value) && $result === sha1($value . $this->csrf_salt); #这个是值进行sha1
        }
        return false;
    }

}
