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

use axguowen\ajcaptcha\core\vo\PointVo;
use axguowen\ajcaptcha\utils\RandomUtils;

/**
 * 文字码数据处理
 */
class ClickData extends BaseData
{
    /**
     * 默认背景图路径
     * @var string
     */
    protected $defaultBackgroundPath = '/resources/images/click/';

    /**
     * 获取坐标
     * @access public
     * @param int $width
     * @param int $height
     * @param int $index
     * @param int $wordCount
     * @return PointVo
     */
    protected function getPoint($width, $height, $index, $wordCount)
    {
        $avgWidth = $width / ($wordCount + 1);
        if ($avgWidth < static::FONTSIZE) {
            $x = RandomUtils::getRandomInt(1 + static::FONTSIZE, $width);
        } else {
            if ($index == 0) {
                $x = RandomUtils::getRandomInt(1 + static::FONTSIZE, $avgWidth * ($index + 1) - static::FONTSIZE);
            } else {
                $x = RandomUtils::getRandomInt($avgWidth * $index + static::FONTSIZE, $avgWidth * ($index + 1) - static::FONTSIZE);
            }
        }
        $y = RandomUtils::getRandomInt(static::FONTSIZE, $height - static::FONTSIZE);
        return new PointVo($x, $y);
    }

    /**
     * 获取坐标列表
     * @access public
     * @param int $width
     * @param int $height
     * @param int $number
     * @return array
     */
    public function getPointList($width, $height, $number = 3): array
    {
        $pointList = [];
        for ($i = 0; $i < $number; $i++) {
            $pointList[] = $this->getPoint($width, $height, $i, $number);
        }
        // 随机排序
        shuffle($pointList);
        return $pointList;
    }


    /**
     * 数组转坐标
     * @access public
     * @param array $list
     * @return array
     */
    public function array2Point($list): array
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = new PointVo($item['x'], $item['y']);
        }
        return $result;
    }

    /**
     * 获取随机字符
     * @access public
     * @param int $number
     * @return array
     */
    public function getWordList($number): array
    {
        return RandomUtils::getRandomChar($number);
    }

    /**
     * 验证
     * @access public
     * @param array $originPointList
     * @param array $targetPointList
     * @return void
     */
    public function check(array $originPointList, array $targetPointList)
    {
        // 是否错误
        $isError = false;
        // 遍历
        foreach ($originPointList as $key => $originPoint) {
            // 如果不存在
            if(!isset($targetPointList[$key])){
                $isError = true;
                break;
            }
            // 当前文字坐标
            $targetPoint = $targetPointList[$key];
            if(
                $targetPoint->x - self::FONTSIZE > $originPoint->x ||
                $targetPoint->x > $originPoint->x + self::FONTSIZE ||
                $targetPoint->y - self::FONTSIZE > $originPoint->y ||
                $targetPoint->y > $originPoint->y + self::FONTSIZE
            ) {
                $isError = true;
                break;
            }
        }

        // 如果未出错
        if(false === $isError){
            return ['验证通过', null];
        }
        // 返回失败
        return [null, new \Exception('验证不通过')];
    }
}
