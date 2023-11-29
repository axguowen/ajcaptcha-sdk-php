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
 * Aes工具类
 */
class AesUtils
{
    /**
     * 加密
     * @access public
     * @param $str
     * @param $secretKey string 只有长度等于16位才能与前端CryptoJS加密一致
     * @return string
     */
    public static function encrypt($str, $secretKey): string
    {
        return base64_encode(openssl_encrypt($str, 'AES-128-ECB', $secretKey, OPENSSL_RAW_DATA));

    }

    /**
     * 解密
     * @access public
     * @param $str
     * @param $secretKey
     * @return string
     */
    public static function decrypt($str, $secretKey): string
    {
        return openssl_decrypt(base64_decode($str), 'AES-128-ECB', $secretKey, OPENSSL_RAW_DATA);
    }

}
