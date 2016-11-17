<?php
namespace swoole\base;

/**
 * 作为Worker和Task进程的基础借口
 */
abstract class Progress
{
    public static $server; #存放服务器对象

    public static $app; #作为服务定位用

    public static $config; #储存配置

    public static $data; #必需销毁的数据存放点，数据只能放在这个里面，不然无法自动销毁；

    /**
     * 根据给出的参数创建对象
     *
     * 示例:
     *
     * ```php
     * // 用一个类名创建对象
     * $object = Progress::createObject('yii\db\Connection');
     *
     * // 用一个配置数组创建对象
     * $object = Progress::createObject([
     *     'class' => 'yii\db\Connection',
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // 用两个参数(对象属性，如$param1就是['属性名'=>'属性值'])创建一个对象
     * $object = \Yii::createObject('MyClass', [$param1, $param2]);
     * ```
     *
     * @param string|array $type 对象. 参数为以下格式:
     *
     * - 字符串: 代表创建的类名，如 swoole\base\Object
     * - 配置数组：这个数组必需包含一个下标'class'的元素，储存的是类名，其余参数为对象属性键值对；
     * @param array $params 构造参数数组
     * @return object 创建的对象
     */
    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return new $type($params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return new $class($type);
        } elseif (is_array($type)) {
            throw new \Exception('Object configuration must be an array containing a "class" element.');
        } else {
            throw new \Exception("Unsupported configuration type: " . gettype($type));
        }
    }
    /*储存yii\log\Logger实例化对象*/


    /**
     * 重新初始化类实例对象
     * Configures an object with the initial config values.
     * @param object $object the object to be configured
     * @param array $config the config initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $config)
    {
        foreach ($config as $name => $value) {
            $object->$name = is_array($object->$name) ? array_merge($object->$name, $value) : $value;
        }
        return $object;
    }

    /**
     * Returns the public member variables of an object.
     * This method is provided such that we can get the public member variables of an object.
     * It is different from "get_object_vars()" 这个是原生PHP 返回由对象属性组成的关联数组
     *  because the latter will return private
     * and protected variables if it is called within the object itself.
     * @param object $object the object to be handled
     * @return array the public member variables of the object
     */
    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }

}
