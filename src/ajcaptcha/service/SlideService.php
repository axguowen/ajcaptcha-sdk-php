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

use axguowen\ajcaptcha\core\vo\PointVo;
use axguowen\ajcaptcha\utils\RandomUtils;

class SlideService extends BaseService
{
    /**
     * 获取滑动验证码
     * @access public
     * @return array
     */
    public function get(): array
    {
        // 获取缓存实例
        $cacheEntity = $this->factory->getCacheInstance();
        // 生成滑动验证码图片实例
        $slideImage = $this->factory->makeSlideImage();
        // 执行创建
        $slideImage->run();
        // 要返回的数据
        $data = [
            'originalImageBase64' => $slideImage->response(),
            'jigsawImageBase64' => $slideImage->response('template'),
            'secretKey' => RandomUtils::getRandomCode(16, 3),
            'token' => RandomUtils::getUUID(),
        ];
        // 设置缓存
        $cacheEntity->set($data['token'], [
            'secretKey' => $data['secretKey'],
            'point' => $slideImage->getPoint()
        ], 7200);

        // 返回
        return $data;
    }

    /**
     * 一次验证
     * @access public
     * @param string $token
     * @param string $pointJson
     * @return array
     */
    public function check($token, $pointJson)
    {
        // 通过token获取缓存数据
        $getCacheDataResult = $this->getCacheData($token);
        // 失败
        if(is_null($getCacheDataResult[0])){
            return $getCacheDataResult;
        }
        // 获取结果
        $cacheData = $getCacheDataResult[0];

        // 数据处理类
        $slideData = $this->factory->makeSlideData();

        // 获取解码出来的前端坐标结果
        $decodePointDataResult = $this->decodePoint($cacheData['secretKey'], $pointJson);
        // 失败
        if(is_null($decodePointDataResult[0])){
            return $decodePointDataResult;
        }
        // 获取结果
        $pointData = $decodePointDataResult[0];
        // 转化为具体坐标
        $targetPoint = new PointVo($pointData['x'], $pointData['y']);

        // 获取校验结果
        $checkResult = $slideData->check($cacheData['point'], $targetPoint);
        // 失败
        if(is_null($checkResult[0])){
            return $checkResult;
        }
        // 设置二次验证缓存
        $this->setSecondVerificationCache($token, $pointJson, $cacheData['secretKey']);
        // 返回
        return ['验证通过', null];
    }

    /**
     * 二次验证
     * @access public
     * @param string $encryptCode
     * @param string $token
     * @param string $pointJson
     * @return array
     */
    public function validate(string $encryptCode, string $token, string $pointJson)
    {
        // 获取缓存实例
        $cacheEntity = $this->factory->getCacheInstance();
        // 获取缓存结果
        $cacheData = $cacheEntity->get($encryptCode);
        // 不存在
        if(empty($cacheData)){
            return [null, new \Exception('参数错误')];
        }
        // 删除缓存
        $cacheEntity->delete($encryptCode);

        // 如果缓存的token与传入的token不一致
        if($token != $cacheData['token']){
            return [null, new \Exception('参数校验失败: token')];
        }

        // 获取解码出来的前端坐标结果
        $decodePointDataResult = $this->decodePoint($cacheData['secretKey'], $pointJson);
        // 失败
        if(is_null($decodePointDataResult[0])){
            return $decodePointDataResult;
        }
        // 获取结果
        $pointData = $decodePointDataResult[0];
        // 如果解码结果与缓存结果不一致
        if($pointData == $cacheData['point']){
            return ['验证通过', null];
        }
        // 返回失败
        return [null, new \Exception('验证不通过')];
    }
}
