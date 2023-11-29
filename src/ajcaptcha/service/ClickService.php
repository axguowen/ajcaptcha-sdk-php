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

use axguowen\ajcaptcha\utils\AesUtils;
use axguowen\ajcaptcha\utils\RandomUtils;

class ClickService extends BaseService
{
    /**
     * 获取文字验证码
     * @access public
     * @return array
     */
    public function get(): array
    {
        // 获取缓存实例
        $cacheEntity = $this->factory->getCacheInstance();
        // 生成文字点击验证码图片实例
        $clickImage = $this->factory->makeClickImage();
        // 执行创建
        $clickImage->run();
        // 要返回的数据
        $data = [
            'originalImageBase64' => $clickImage->response(),
            'wordList' => array_slice($clickImage->getWordList(), 0, $this->factory->getConfig('click_check_length')),
            'secretKey' => RandomUtils::getRandomCode(16, 3),
            'token' => RandomUtils::getUUID()
        ];
        // 设置缓存
        $cacheEntity->set($data['token'], [
            'secretKey' => $data['secretKey'],
            'point' => $clickImage->getPoint()
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
        // 通过token获取原始数据
        $getCacheDataResult = $this->getCacheData($token);
        // 失败
        if(is_null($getCacheDataResult[0])){
            return $getCacheDataResult;
        }
        // 获取结果
        $cacheData = $getCacheDataResult[0];

        // 生成文字数据实例
        $clickData = $this->factory->makeClickData();

        // 获取解码出来的前端坐标结果
        $decodePointDataResult = $this->decodePoint($cacheData['secretKey'], $pointJson);
        // 失败
        if(is_null($decodePointDataResult[0])){
            return $decodePointDataResult;
        }
        // 获取结果
        $pointData = $decodePointDataResult[0];
        // 转化为具体坐标
        $targetPointList = $clickData->array2Point($pointData);

        // 获取校验结果
        $checkResult = $clickData->check(array_slice($cacheData['point'], 0, $this->factory->getConfig('click_check_length')), $targetPointList);
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
