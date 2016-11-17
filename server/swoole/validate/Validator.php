<?php
namespace swoole\validate;

/**
 * 数据验证类
 */
use swoole\base\Object;

class Validator extends Object
{

    /**
     * @var string 进行常规的验证
     * @see http://www.regular-expressions.info/email.html
     */
    public $email_pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';

    /**
     * @var string 验证常规也可以有名字的邮箱
     *  (如："John Smith <john.smith@example.com>"). 默认不验证.
     */
    public $email_fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';

    /**
     * 验证邮箱格式
     * @param  [string]  $email    邮箱字符串
     * @param  boolean $allowName  是否验证包含有名字的邮箱，默认为不验证
     * @return [boolean]           返回验证是否成功
     */
    public function validateEmail($email, $allowName = false)
    {
        // 检查长度，防止dos攻击
        if (!is_string($email) || strlen($email) >= 320) {
            $valid = false;
        } elseif (!preg_match('/^(.*<?)(.*)@(.*?)(>?)$/', $email, $matches)) {
            $valid = false;
        } else {
            $domain = $matches[3];
            $valid  = preg_match($this->email_pattern, $email) || $allowName && preg_match($this->email_fullPattern, $email);
        }

        return $valid;
    }

    /**
     * @var string url常规用于验证的语法，包含的`{schemes}`会被`url_valid_schemes`属性替换
     */
    public $url_pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
    /**
     * @var array 被验证的URI模式，默认为http和https，不区分大小写；
     */
    public $url_valid_schemes = ['http', 'https'];
    /**
     * @var string 默认的URI模式，如：http或者https，如果设置了这个属性，
     * 可以判断 http://www.baidu.com ，https://www.baidu.com ，还可以验证 www.baidu.com。
     */
    public $url_default_scheme;

    /**
     * 验证url
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function validateUrl($value)
    {
        // make sure the length is limited to avoid DOS attacks
        if (is_string($value) && strlen($value) < 2000) {
            if ($this->url_default_scheme !== null && strpos($value, '://') === false) {
                $value = $this->url_default_scheme . '://' . $value;
            }

            if (strpos($this->url_pattern, '{schemes}') !== false) {
                $url_pattern = str_replace('{schemes}', '(' . implode('|', $this->url_valid_schemes) . ')', $this->url_pattern);
            } else {
                $url_pattern = $this->url_pattern;
            }
            if (preg_match($url_pattern, $value)) {
                return true;
            }
        }

        return false;
    }
    /**
     * 手机号验证模式
     * @var string
     */
    public $mobile_pattern = '/^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/';

    /**
     * 验证手机号码
     * @param  int $value 手机号码
     * @return [boolean]        [是否为手机号码]
     */
    public function validateMobile($value)
    {
        if ((string) $value && strlen($value) == 11) {
            if (preg_match($this->mobile_pattern, $value)) {
                return true;
            }
        }

        return false;
    }

}
