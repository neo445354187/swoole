<?php
/**
 * 说明：这里是说明
 * @Author          chao <1303582949@qq.com>
* @DateTime        2016-06-10 13:49:39
 * @package         /swoole/base/
 * @copyright          Copyright
 * @license         http://www.test.com/license/
 * @since              1.0
 * @Description     对象基类
 */
namespace swoole\base;

use swoole\base\Progress;

/**
 * 所有类的基类
 */
class Object
{
    /**
     * 返回类全名
     * @return string 类全名
     */
    public static function className()
    {
        return get_called_class();//get_called_class — 后期静态绑定（"Late Static Binding"）类的名称
    }

    /**
     * 先初始化对象，利用Progress::configure
     *
     *  如果这个方法被子类覆盖，那么$config作为最后一个形参，并且在方法体的最后调用parent::__construct()
     *
     * @param array $config name-value 键值对来初始化对象
     */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            Progress::configure($this, $config);
        }
        $this->init(); //调用init();
    }

    /**
     * 初始化完对象后，第一步自动调用的方法；
     * 
     */
    public function init()
    {
    }

    /**
     * 返回对象的属性值，利用调用对象的方法，方法名为 'get'.$name()
     * 讲解：其实是通过组装的方法去操作某个属性
     * @param string $name 属性名
     * @return mixed 属性值
     * @throws BadMethodCallException 如果找不到组合的方法(即 'get'.$name)，则抛出异常
     * @throws Exception 如果属性只写，抛出的异常
     * @see __set()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new \BadMethodCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new \Exception('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * 设置对象的属性值
     * 讲解：其实是通过组装的方法去操作某个属性
     * 
     * @param string $name 属性名
     * @param mixed $value 属性值
     * @throws BadMethodCallException 如果属性没有定义，抛出异常
     * @throws Exception 如果属性只读，则抛出的异常
     * @see __get()
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new \BadMethodCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new \Exception('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * 判断属性是否存在，其实是判断组合方法(即'get'.$name)是否存在
     *
     * @param string $name 属性名
     * @return boolean 没有则返回false
     * @see http://php.net/manual/en/function.isset.php
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return false;
        }
    }

    /**
     * 设置某个属性为NULL
     * 如果该属性只读，则抛出异常
     * @param string $name 属性名
     * @throws BadMethodCallException 如果属性只读，抛出的异常
     * @see http://php.net/manual/en/function.unset.php
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new \BadMethodCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * 调用方法不存在时抛出异常
     * 
     * @param string $name 方法名
     * @param array $params 方法参数
     * @throws BadMethodCallException 当未知方法被调用时，抛出此异常
     * @return mixed 抛出异常
     */
    public function __call($name, $params)
    {
        throw new \BadMethodCallException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * 查看属性是否存在
     * 有以下情况：
     *      要么'get'.$name组装方法或者'set'.$name方法存在
     *      要么在$checkVars == true时，类的成员变量或者对象的属性存在
     *
     * @param string $name 属性名
     * @param boolean $checkVars 是否检查类的成员变量或者对象的属性存在
     * @return boolean 返回属性是否被定义
     * @see canGetProperty()
     * @see canSetProperty()
     */
    public function hasProperty($name, $checkVars = true)
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }

    /**
     * 检查是否该对象属性或者类成员变量可读(返回boolean)
     * 有以下情况：
     *     要么组合方法('get'.$name)存在
     *     要么在$checkVars == true时，类的成员变量或者对象的属性存在
     *
     * @param string $name 属性名
     * @param boolean $checkVars 是否检查类的成员变量或者对象的属性存在
     * @return boolean 返回属性是否可读
     * @see canSetProperty()
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * 检查是否该对象属性或者类成员变量可写(返回boolean)
     * 有以下情况：
     *     要么组合方法('set'.$name)存在
     *     要么在$checkVars == true时，类的成员变量或者对象的属性存在
     *
     * @param string $name 属性名
     * @param boolean $checkVars 是否检查类的成员变量或者对象的属性存在
     * @return boolean 表明是否可写
     * @see canGetProperty()
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * 检查方法是否被定义
     *
     * 现在默认是执行`method_exists()`，当然你也可以配合`__call()`，重新制定检验规则
     * @param string $name 方法名
     * @return boolean 返回方法是否被定义
     */
    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }
}
