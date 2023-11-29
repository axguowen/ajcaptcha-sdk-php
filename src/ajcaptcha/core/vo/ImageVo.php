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

namespace axguowen\ajcaptcha\core\vo;

use axguowen\ajcaptcha\utils\MathUtils;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic;

abstract class ImageVo
{
    /**
     * 图片实例
     * @var Image
     */
    public $image;

    /**
     * 链接
     * @var string
     */
    public $src;

    /**
     * 位置的映射
     * @var array
     */
    protected $pickMaps = [];

    /**
     * 完成的回调
     * @var \Closure
     */
    protected $finishCallback;

    /**
     * 构造方法
     * @access public
     * @param string $src
     * @return void
     */
    public function __construct($src)
    {
        $this->src = $src;
        $this->initImage($src);
    }

    /**
     * 生成图片
     * @access public
     * @param string $src
     * @return void
     */
    public function initImage($src)
    {
        $this->image = ImageManagerStatic::make($src);
    }

    /**
     * 获取图片中某一个位置的rgba值
     * @access public
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getPickColor($x, $y): array
    {
        if (!isset($this->pickMaps[$x][$y])) {
            $this->pickMaps[$x][$y] = $this->image->pickColor($x, $y);
        }
        return $this->pickMaps[$x][$y];
    }


    /**
     * 设置图片指定位置的颜色值
     * @access public
     * @param string $color
     * @param int $x
     * @param int $y
     * @return void
     */
    public function setPixel($color, $x, $y)
    {
        $this->image->pixel($color, $x, $y);
    }

    /**
     * 获取坐标值
     * @access public
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getBlurValue(int $x, int $y): array
    {
        $image = $this->image;
        $red = [];
        $green = [];
        $blue = [];
        $alpha = [];
        // 边框取5个点，4个角取3个点，其余取8个点
        foreach ([
            [0, 1], [0, -1],
            [1, 0], [-1, 0],
            [1, 1], [1, -1],
            [-1, 1], [-1, -1],
        ] as $distance){
            $pointX = $x + $distance[0];
            $pointY = $y + $distance[1];
            if ($pointX < 0 || $pointX >= $image->getWidth() || $pointY < 0 || $pointY >= $image->height()) {
                continue;
            }
            [$r, $g, $b, $a] = $this->getPickColor($pointX, $pointY);
            $red[] = $r;
            $green[] = $g;
            $blue[] = $b;
            $alpha[] = $a;
        }
        return [MathUtils::avg($red), MathUtils::avg($green), MathUtils::avg($blue), MathUtils::avg($alpha)];
    }


    /**
     * 是否不透明
     * @access public
     * @param int $x
     * @param int $y
     * @return bool
     */
    public function isOpacity($x, $y): bool
    {
        return $this->getPickColor($x, $y)[3] > 0.5;
    }

    /**
     * 是否为边框
     * @access public
     * @param bool $isOpacity
     * @param int $x
     * @param int $y
     * @return bool
     */
    public function isBoundary(bool $isOpacity, int $x, int $y): bool
    {
        $image = $this->image;
        if ($x >= $image->width() - 1 || $y >= $image->height() - 1) {
            return false;
        }
        $right = [$x + 1, $y];
        $down = [$x, $y + 1];
        if (
            $isOpacity && !$this->isOpacity(...$right) ||
            !$isOpacity && $this->isOpacity(...$right) ||
            $isOpacity && !$this->isOpacity(...$down) ||
            !$isOpacity && $this->isOpacity(...$down)
        ) {
            return true;
        }
        return false;
    }

    /**
     * 模糊图片
     * @access public
     * @param $targetX
     * @param $targetY
     * @return void
     */
    public function vagueImage($targetX, $targetY)
    {
        $blur = $this->getBlurValue($targetX, $targetY);
        $this->setPixel($blur, $targetX, $targetY);
    }


    /**
     * 获取坐标点
     * @access public
     * @return array
     */
    public function getPickMaps(): array
    {
        return $this->pickMaps;
    }

    /**
     * 设置坐标点
     * @access public
     * @param array $pickMaps
     * @return void
     */
    public function setPickMaps(array $pickMaps): void
    {
        $this->pickMaps = $pickMaps;
    }

    /**
     * 提前初始化像素
     * @access public
     * @return void
     */
    public function preparePickMaps()
    {
        $width = $this->image->getWidth();
        $height = $this->image->getHeight();
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $this->getPickColor($x, $y);
            }
        }
    }

    /**
     * 设置完成回调
     * @access public
     * @return void
     */
    public function setFinishCallback($finishCallback){
        $this->finishCallback = $finishCallback;
    }

    /**
     * 析构方法
     * @access public
     * @return void
     */
    public function __destruct()
    {
        if(!empty($this->finishCallback) && $this->finishCallback instanceof \Closure){
            ($this->finishCallback)($this);
        }
    }
}