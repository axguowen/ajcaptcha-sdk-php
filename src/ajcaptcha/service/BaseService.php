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

namespace axguowen\ajcaptcha\service;

use axguowen\ajcaptcha\core\Factory;
use axguowen\ajcaptcha\utils\AesUtils;

abstract class BaseService
{
    /**
     * 配置
     * @var array
     */
    protected $options = [];

    /**
     * 工厂对象
     * @var Factory
     */
    protected $factory;

    /**
     * 构造方法
     * @access public
     * @param array $options
     * @return void
     */
    public function __construct($options)
    {
        $this->factory = new Factory($options);
    }

    /**
     * 获取验证码
     * @access public
     * @return array
     */
    abstract public function get();

    /**
     * 一次验证
     * @access public
     * @param string $token
     * @param string $pointJson
     * @return array
     */
    abstract public function check($token, $pointJson);

    /**
     * 验证验证码
     * @access public
     * @param string $encryptCode
     * @param string $token
     * @param string $pointJson
     * @return void
     */
    abstract public function validate(string $encryptCode, string $token, string $pointJson);

    /**
     * 设置二次验证缓存
     * @access protected
     * @param string $token
     * @param string $pointJson
     * @param string $secretKey
     * @return string
     */
    protected function setSecondVerificationCache($token, $pointJson, $secretKey)
    {
        // 获取缓存对象实例
        $cacheEntity = $this->factory->getCacheInstance();
        // 解码坐标
        $pointStr = AesUtils::decrypt($pointJson, $secretKey);
        // 生成缓存标识
        $key = AesUtils::encrypt($token . '---' . $pointStr, $secretKey);
        // 设置二次验证缓存
        $cacheEntity->set($key, [
            'token' => $token,
            'point' => $pointJson,
            'secretKey' => $secretKey,
        ], 900);
        // 删除一次验证缓存
        $cacheEntity->delete($token);
    }

    /**
     * 解码坐标点
     * @access protected
     * @param string $secretKey
     * @param string $point
     * @return array
     */
    protected function decodePoint($secretKey, $point): array
    {
        // 解密
        $pointJson = AesUtils::decrypt($point, $secretKey);
        // 失败
        if ($pointJson == false) {
            return [null, new \Exception('aes验签失败')];
        }
        // 返回结果
        return [json_decode($pointJson, true), null];
    }

    /**
     * 获取缓存数据
     * @access protected
     * @param string $token
     * @return array
     */
    protected function getCacheData($token)
    {
        // 获取缓存实例
        $cacheEntity = $this->factory->getCacheInstance();
        // 获取缓存数据
        $cacheData = $cacheEntity->get($token);
        // 如果缓存不存在
        if (!empty($cacheData)) {
            return [$cacheData, null];
        }
        return [null, new \Exception('参数校验失败：token')];
    }
}
