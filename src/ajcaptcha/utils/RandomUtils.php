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
 * 随机字符串工具类
 */
class RandomUtils
{
    /**
     * 获取随机数
     * @access public
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function getRandomInt($min, $max): int
    {
        try {
            return random_int(intval($min), intval($max));
        }catch (\Exception $e){
            return mt_rand($min, $max);
        }
    }

    /**
     * 随机获取眼色值
     * @access public
     * @return array
     */
    public static function getRandomColor(): array
    {
         return [self::getRandomInt(1, 255), self::getRandomInt(1, 255), self::getRandomInt(1, 255)];
    }

    /**
     * 随机获取角度
     * @access public
     * @param int $start
     * @param int $end
     * @return int
     */
    public static function getRandomAngle(int $start = -45, int $end = 45): int
    {
         return self::getRandomInt($start, $end);
    }

    /**
     * 随机获取汉字
     * @access public
     * @param int $num 生成汉字的数量
     * @return array
     */
    public static function getRandomChar(int $num): array
    {
        $b = [];
        for ($i=0; $i<$num; $i++) {
            // 使用chr()函数拼接双字节汉字，前一个chr()为高位字节，后一个为低位字节
            $a = chr(self::getRandomInt(0xB0,0xD0)).chr(self::getRandomInt(0xA1, 0xF0));
            // 转码
            $h = iconv('GB2312', 'UTF-8', $a);
            if(!in_array($h, $b)){
                $b[] = $h;
            }else{
                $i--; //去重
            }
        }
        return $b;
    }

    /**
     * 类似java一样的uuid
     * @access public
     * @param string $prefix
     * @return string
     */
    public static function getUUID(string $prefix = ''): string
    {
        $chars = md5(uniqid((string) self::getRandomInt(1, 100), true));
        $uuid  = substr($chars,0,8) . '-';
        $uuid .= substr($chars,8,4) . '-';
        $uuid .= substr($chars,12,4) . '-';
        $uuid .= substr($chars,16,4) . '-';
        $uuid .= substr($chars,20,12);
        return $prefix . $uuid;
    }

    /**
     * 获取随机字符串编码
     * @access public
     * @param int $length 字符串长度
     * @param int $type 字符串类型(1纯数字,2纯字母,3数字字母)
     * @return string
     */
    public static function getRandomCode(int $length = 10, int $type = 1): string
    {
        $numbs = '0123456789';
        $chars = "abcdefghilkmnopqrstuvwxyz";
        $maps = '';
        if ($type === 1){
            $maps = $numbs;
        }
        if ($type === 2){
            $maps = $chars;
        }
        if ($type === 3){
            $maps = "{$numbs}{$chars}";
        }
        $string = $maps[self::getRandomInt(1, strlen($maps) - 1)];
        while (strlen($string) < $length) {
            $string .= $maps[self::getRandomInt(0, strlen($maps) - 1)];
        }
        return $string;
    }
}
