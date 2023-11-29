<?php
// +----------------------------------------------------------------------
// | AJ-Captcha SDK [AJ-Captcha SDK for PHP]
// +----------------------------------------------------------------------
// | AJ-Captcha SDK
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

namespace axguowen\ajcaptcha\core\logic;

use axguowen\ajcaptcha\utils\CacheUtils;

class Cache
{
    /**
     * 配置
     * @var array
     */
    protected $options = [
        // 驱动类
        'handler' => CacheUtils::class,
        // 方法映射
        'method_map' => [],
        // 额外选项
        'options' => [],
    ];

    /**
     * 驱动
     * @var mixed
     */
    protected $driver;

    /**
     * 方法映射
     * @var array
     */
    protected $methodMap = [
        'get' => 'get',
        'set' => 'set',
        'delete' => 'delete',
        'has' => 'has'
    ];

    /**
     * 构造方法
     * @access public
     * @param array $config
     * @return void
     */
    public function __construct($config = [])
    {
        // 合并配置
        $this->options = array_merge($this->options, $config);
        // 合并方法映射
        $this->methodMap = array_merge($this->methodMap, $this->options['method_map']);
        // 获取缓存驱动
        $this->driver = $this->getDriver($this->options['handler'], $this->options['options']);
    }

    /**
     * 获取驱动实例
     * @access public
     * @param mixed $callback
     * @param array $options
     * @return void
     */
    public function getDriver($callback, $options = [])
    {
        if ($callback instanceof \Closure) {
            $result = $callback($options);
        } else if (is_object($callback)) {
            $result = $callback;
        } else if (is_array($callback)) {
            $result = call_user_func($callback, $options);
        } else if ($this->isSerialized($callback)) {
            $result = unserialize($callback);
        } else if (is_string($callback) && class_exists($callback)) {
            $result = new $callback($options);
        } else {
            $result = new CacheUtils($options);
        }
        return $result;
    }

    /**
     * 是否可以被反序列化
     * @access public
     * @param mixed $data
     * @return bool
     */
    public function isSerialized($data): bool
    {
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a' :
            case 'O' :
            case 's' :
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                    return true;
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                    return true;
                break;
        }
        return false;
    }

    /**
     * 通过映射获取真实的驱动方法
     * @access public
     * @param string $name
     * @return string
     */
    public function getDriverMethod($name)
    {
        return $this->methodMap[$name];
    }

    /**
     * 获取缓存
     * @access public
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $method = $this->getDriverMethod('get');
        return $this->execute($method, [$key,$default]);
    }

    /**
     * 设置缓存
     * @access public
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl = null)
    {
        $method = $this->getDriverMethod('set');
        return $this->execute($method, [$key, $value, $ttl]);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $key
     * @return mixed
     */
    public function delete($key)
    {
        $method = $this->getDriverMethod('delete');
        return $this->execute($method, [$key]);
    }

    /**
     * 缓存是否存在
     * @access public
     * @param string $key
     * @return mixed
     */
    public function has($key)
    {
        $method = $this->getDriverMethod('has');
        return $this->execute($method, [$key]);
    }

    /**
     * 执行缓存驱动方法
     * @access protected
     * @param string $method
     * @param array $params
     * @return mixed
     */
    protected function execute(string $method, array $params){
        return $this->driver->$method(...$params);
    }
}
