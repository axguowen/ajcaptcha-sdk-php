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

namespace axguowen\ajcaptcha\utils;

/**
 * 计算工具类
 */
class MathUtils
{
    /**
     * 获取平均值
     * @access public
     * @param array $array
     * @return int
     */
    public static function avg(array $array): int
    {
        return intval(array_sum($array) / count($array));
    }
}