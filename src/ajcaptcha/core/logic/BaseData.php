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

use axguowen\ajcaptcha\core\vo\BackgroundVo;
use axguowen\ajcaptcha\utils\RandomUtils;
use Intervention\Image\ImageManagerStatic;

class BaseData
{
    // 字体大小
    public const FONTSIZE = 24;

    /**
     * 默认背景图片路径
     * @var string
     */
    protected $defaultBackgroundPath;

    /**
     * 默认字体路径
     * @var string
     */
    protected $defaultFontPath = '/resources/fonts/WenQuanZhengHei.ttf';

    /**
     * 获取字体包文件
     * @access public
     * @param string $file
     * @return string
     */
    public function getFontFile(string $file = ''): string
    {
        return $file && is_file($file) ? $file : dirname(__DIR__, 2) . $this->defaultFontPath;
    }

    /**
     * 获得随机图片
     * @access protected
     * @param array $images
     * @return string
     */
    protected function getRandImage($images): string
    {
        $index = RandomUtils::getRandomInt(0, count($images) - 1);
        return $images[$index];
    }

    /**
     * 获取默认图片
     * @access protected
     * @param string $dir
     * @param array|string $images
     * @return array|false
     */
    protected function getDefaultImage($dir, $images)
    {
        if (!empty($images)) {
            if (is_array($images)) {
                return $images;
            }
            if (is_string($images)) {
                $dir = $images;
            }
        }
        return glob($dir . '*.png');
    }

    /**
     * 获取一张背景图地址
     * @access public
     * @param array|string|null $backgrounds 背景图库
     * @return BackgroundVo
     */
    public function getBackgroundVo($backgrounds = null): BackgroundVo
    {
        // 获取背景图目录
        $dir = dirname(__DIR__, 2) . $this->defaultBackgroundPath;
        // 获取默认图片列表
        $backgrounds = $this->getDefaultImage($dir, $backgrounds);
        // 随机获取一张图片
        $src = $this->getRandImage($backgrounds);
        // 实例化图片并返回
        return new BackgroundVo($src);
    }
}
