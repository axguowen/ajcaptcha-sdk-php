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
use axguowen\ajcaptcha\core\vo\OffsetVo;
use axguowen\ajcaptcha\core\vo\TemplateVo;
use axguowen\ajcaptcha\utils\RandomUtils;

class SlideData extends BaseData
{
    /**
     * 默认背景图片路径
     * @var string
     */
    protected $defaultBackgroundPath = '/resources/images/slide/background/';

    /**
     * 默认偏移量
     * @var mixed
     */
    protected $faultOffset;

    /**
     * 获取默认偏移量
     * @access public
     * @return mixed
     */
    public function getFaultOffset()
    {
        return $this->faultOffset;
    }

    /**
     * 设置默认偏移量
     * @access public
     * @param $this
     */
    public function setFaultOffset($faultOffset): self
    {
        $this->faultOffset = $faultOffset;
        return $this;
    }

    /**
     * 获取剪切模板Vo
     * @access public
     * @param BackgroundVo $backgroundVo
     * @param array $templates
     * @return TemplateVo
     */
    public function getTemplateVo(BackgroundVo $backgroundVo, array $templates = []): TemplateVo
    {
        // 背景图
        $background = $backgroundVo->image;
        // 初始偏移量，让模板图在背景的右1/2位置
        $bgWidth = intval($background->getWidth() / 2);
        // 随机获取一张图片
        $src = $this->getRandImage($this->getTemplateImages($templates));
        // 实例化模板Vo
        $templateVo = new TemplateVo($src);
        // 随机获取偏移量
        $offset = RandomUtils::getRandomInt(0, $bgWidth - $templateVo->image->getWidth() - 1);
        // 设置偏移量
        $templateVo->setOffset(new OffsetVo($offset + $bgWidth, 0));
        // 返回
        return $templateVo;
    }

    /**
     * 获取干扰图模板Vo
     * @access public
     * @param BackgroundVo $backgroundVo
     * @param TemplateVo $templateVo
     * @param array $templates
     * @return TemplateVo
     */
    public function getInterfereVo(BackgroundVo $backgroundVo, TemplateVo $templateVo, $templates = []): TemplateVo
    {
        // 背景
        $background = $backgroundVo->image;
        // 模板库去重
        $templates = $this->exclude($this->getTemplateImages($templates), $templateVo->src);
        // 随机获取一张模板图
        $src = $this->getRandImage($templates);
        // 实例化模板Vo
        $interfereVo = new TemplateVo($src);
        // 最大偏移量
        $maxOffsetX = intval($templateVo->image->getWidth() / 2);
        do {
            // 随机获取偏移量
            $offsetX = RandomUtils::getRandomInt(0, $background->getWidth() - $templateVo->image->getWidth() - 1);

            // 不与原模板重复
            if (abs($templateVo->offset->x - $offsetX) > $maxOffsetX) {
                $interfereVo->setOffset(new OffsetVo($offsetX, 0));
                return $interfereVo;
            }
        } while (true);
    }

    /**
     * 获取模板背景图
     * @access protected
     * @param array $templates
     * @return array|false
     */
    protected function getTemplateImages(array $templates = [])
    {
        $dir = dirname(__DIR__, 2) . '/resources/images/slide/template/';
        return $this->getDefaultImage($dir, $templates);
    }

    /**
     * 排除
     * @access protected
     * @param array $templates
     * @param string $exclude
     * @return array
     */
    protected function exclude($templates, $exclude): array
    {
        if (false !== ($key = array_search($exclude, $templates))) {
            array_splice($templates,$key,1);
        }
        return $templates;
    }

    /**
     * 验证
     * @access public
     * @param $originPoint
     * @param $targetPoint
     * @return array
     */
    public function check($originPoint, $targetPoint)
    {
        // 验证通过
        if (abs($originPoint->x - $targetPoint->x) <= $this->faultOffset && $originPoint->y == $targetPoint->y) {
            return ['验证通过', null];
        }
        // 返回失败
        return [null, new \Exception('验证不通过')];
    }
}
