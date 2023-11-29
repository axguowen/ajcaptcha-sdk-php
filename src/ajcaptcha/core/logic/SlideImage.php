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
use axguowen\ajcaptcha\core\vo\ImageVo;
use axguowen\ajcaptcha\core\vo\PointVo;
use axguowen\ajcaptcha\core\vo\TemplateVo;

class SlideImage extends BaseImage
{
    // 白色
    const WHITE = [255, 255, 255, 1];

    /**
     * 模板图vo
     * @var TemplateVo
     */
    protected $templateVo;

    /**
     * 干扰图vo
     * @var TemplateVo
     */
    protected $interfereVo;


    /**
     * 获取模板图vo
     * @access public
     * @return TemplateVo
     */
    public function getTemplateVo(): TemplateVo
    {
        return $this->templateVo;
    }

    /**
     * 设置模板图vo
     * @access public
     * @param TemplateVo $templateVo
     * @return $this
     */
    public function setTemplateVo(TemplateVo $templateVo)
    {
        $this->templateVo = $templateVo;
        return $this;
    }

    /**
     * 获取干扰图vo
     * @access public
     * @return TemplateVo
     */
    public function getInterfereVo(): TemplateVo
    {
        return $this->interfereVo;
    }

    /**
     * 设置干扰图vo
     * @access public
     * @param TemplateVo $interfereVo
     * @return $this
     */
    public function setInterfereVo(TemplateVo $interfereVo)
    {
        $this->interfereVo = $interfereVo;
        return $this;
    }

    /**
     * 运行
     * @access public
     * @return void
     */
    public function run()
    {
        $flag = false;
        $this->cutByTemplate($this->templateVo, $this->backgroundVo, function ($param) use (&$flag) {
            if (! $flag) {
                // 记录第一个点, 前端已将y值写死
                $this->setPoint(new PointVo($param[0], 5));
                $flag = true;
            }
        });
        // 剪切模板
        $this->cutByTemplate($this->interfereVo, $this->backgroundVo);
        // 生成水印
        $this->makeWatermark($this->backgroundVo->image);
    }

    /**
     * 剪切模板
     * @access public
     * @param TemplateVo $interfereVo
     * @param BackgroundVo $backgroundVo
     * @param \Closure $callable
     * @return void
     */
    public function cutByTemplate(TemplateVo $templateVo, BackgroundVo $backgroundVo, $callable = null)
    {
        $template = $templateVo->image;
        $width = $template->getWidth();
        $height = $template->getHeight();
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                // 背景图对应的坐标
                $bgX = $x + $templateVo->offset->x;
                $bgY = $y + $templateVo->offset->y;
                // 是否不透明
                $isOpacity = $templateVo->isOpacity($x, $y);
                if ($isOpacity) {
                    // 如果不透明
                    if ($callable instanceof \Closure) {
                        $callable([$bgX, $bgY]);
                    }
                    // 模糊背景图选区
                    $backgroundVo->vagueImage($bgX, $bgY);
                    // 复制颜色
                    $this->copyPickColor($backgroundVo,$bgX,$bgY, $templateVo, $x, $y);
                }
                if ($templateVo->isBoundary($isOpacity, $x, $y)) {
                    $backgroundVo->setPixel(self::WHITE, $bgX, $bgY);
                    $templateVo->setPixel(self::WHITE, $x, $y);
                }
            }
        }
    }

    /**
     * 把$source的颜色复制到$target上
     * @access protected
     * @param ImageVo $source
     * @param ImageVo $target
     * @return void
     */
    protected function copyPickColor(ImageVo $source, $sourceX, $sourceY, ImageVo $target, $targetX, $targetY)
    {
        $bgRgba = $source->getPickColor($sourceX, $sourceY);
        $target->setPixel($bgRgba, $targetX, $targetY);//复制背景图片给模板
    }

    /**
     * 返回前端需要的格式
     * @access public
     * @param string $type
     * @return false|string
     */
    public function response($type = 'background')
    {
        $image = $type == 'background' ? $this->backgroundVo->image : $this->templateVo->image;
        $result = $image->encode('data-url')->getEncoded();
        // 返回图片base64的第二部分
        return explode(',', $result)[1];
    }

    /**
     * 用来调试
     * @access public
     * @param string $type
     * @return void
     */
    public function echo($type = 'background')
    {
        $image = $type == 'background' ? $this->backgroundVo->image : $this->templateVo->image;
        die($image->response());
    }
}
