<?php
namespace swoole\rbac;

/**
 * 权限类
 * 权利判断依赖于nginx板块中的代码，如tp中的权限判断，tp原生是将权限存在session里，现在需要直接存在
 * redis中，利用html中的auth_token，经过$auth_token.$this->csrf_salt组装，再sha1加密为键，权限组为值
 * 判断需要执行的操作是否在权限组(数组转化的json字符串)里，在，则该用户具有该权限，否则提示用户不具备该权限
 *
 */
use swoole\base\Object;
use swoole\base\Progress;

class Auth extends Object
{
    /**
     * [$auth_salt 用于权限验证的auth_token验证，这个要与nginx里的设置redis中标记的代码这个部分相同才行]
     * @var string
     */
    private $auth_salt = "{zcyx9wl@";

    /**
     * 设置过期时间
     * @var integer
     */
    public $expire = 1440;

    /**
     * 进行权限验证
     * 说明：这里参数$token为html的auth_token，既用于swoole板块的权限验证，也用于nginx板块的权限验证
     * 权限验证后，无论是否验证成功，都延迟权限数据过期时间
     * @param  [string] $value [要进行实体化的字符串]
     * @return [string]        返回已经实体化的字段
     */
    public function checkAuth($token, $class, $action)
    {
        $key = sha1($value . $this->auth_salt);         #这个是键进行sha1
        if ($result = Progress::$app['cache']->get($key)) {
            Progress::$app['cache']->expire($key, $this->expire);
            return in_array(strtr($class . '/' . $action, '\\', '/'), json_decode($result, true));
        }
        return false;
    }

}
