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
use Intervention\Image\AbstractFont as Font;
use Intervention\Image\Image;

abstract class BaseImage
{
    /**
     * 水印配置
     * @var array
     */
    protected $watermark = [
        // 水印文字内容
        'text' => '',
        // 水印文字大小
        'fontsize' => 12,
        // 水印文字颜色
        'color' => '#ffffff',
    ];

    /**
     * 背景图实例
     * @var BackgroundVo
     */
    protected $backgroundVo;

    /**
     * 字体文件路径
     * @var string
     */
    protected $fontFile;

    /**
     * 水印位置
     * @var mixed
     */
    protected $point;

    /**
     * 获取水印位置
     * @access public
     * @return mixed
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * 设置水印位置
     * @access public
     * @param mixed $point
     * @return $this
     */
    public function setPoint($point)
    {
        $this->point = $point;
        return $this;
    }

    /**
     * 生成水印图片
     * @access protected
     * @param Image $image
     * @return void
     */
    protected function makeWatermark(Image $image)
    {
        // 如果设置了文字水印
        if (!empty($this->watermark['text'])) {
            // 1汉字3个字节;汉字长宽比约0.618;fontsize是以高度来计算像素的
            $offsetX = intval(strlen($this->watermark['text']) / 3 * $this->watermark['fontsize'] * 0.618);
            $offsetY = intval($this->watermark['fontsize'] / 2);
            $x = $image->getWidth() - $offsetX;
            $y = $image->getHeight() - $offsetY - 5;
            $image->text($this->watermark['text'], $x, $y, function (Font $font) {
                $font->file($this->fontFile);
                $font->size($this->watermark['fontsize']);
                $font->color($this->watermark['color']);
                $font->align('center');
                $font->valign('center');
            });
        }
    }

    /**
     * 设置水印文字内容
     * @access public
     * @param array $watermark
     * @return $this
     */
    public function setWatermark(array $watermark)
    {
        // 如果设置了文字
        if(isset($watermark['text']) && !empty($watermark['text'])){
            $this->watermark['text'] = $watermark['text'];
        }
        // 如果设置了字体大小
        if(isset($watermark['fontsize']) && $watermark['fontsize'] > 0){
            $this->watermark['fontsize'] = $watermark['fontsize'];
        }
        // 如果设置了文字颜色
        if(isset($watermark['color']) && !empty($watermark['color'])){
            $this->watermark['color'] = $watermark['color'];
        }
        // 返回
        return $this;
    }


    /**
     * 设置水印背景图
     * @access public
     * @param BackgroundVo $backgroundVo
     * @return $this
     */
    public function setBackgroundVo(BackgroundVo $backgroundVo)
    {
        $this->backgroundVo = $backgroundVo;
        return $this;
    }

    /**
     * 获取水印背景图
     * @access public
     * @return BackgroundVo
     */
    public function getBackgroundVo(): BackgroundVo
    {
        return $this->backgroundVo;
    }

    /**
     * 设置字体文件路径
     * @access public
     * @param string $file
     * @return $this
     */
    public function setFontFile($file)
    {
        $this->fontFile = $file;
        return $this;
    }

    /**
     * 运行
     * @access public
     * @return mixed
     */
    public abstract function run();
}
