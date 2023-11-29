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

namespace axguowen\ajcaptcha\core;

use axguowen\ajcaptcha\core\logic\BaseData;
use axguowen\ajcaptcha\core\logic\BaseImage;
use axguowen\ajcaptcha\core\logic\SlideImage;
use axguowen\ajcaptcha\core\logic\ClickImage;
use axguowen\ajcaptcha\core\logic\SlideData;
use axguowen\ajcaptcha\core\logic\ClickData;
use axguowen\ajcaptcha\core\logic\Cache;
use axguowen\ajcaptcha\core\vo\ImageVo;
use Intervention\Image\ImageManagerStatic;

class Factory
{
    /**
     * 配置
     * @var array
     */
    protected $options = [];

    /**
     * 缓存实例
     * @var mixed
     */
    protected $cacheInstance;

    /**
     * 构造方法
     * @access public
     * @param array $options
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * 生成图片验证码
     * @access public
     * @return SlideImage
     */
    public function makeSlideImage(): SlideImage
    {
        $data = new SlideData();
        $image = new SlideImage();
        $this->setCommon($image, $data);
        $this->setSlide($image, $data);
        return $image;
    }

    /**
     * 生成文字验证码
     * @access public
     * @return ClickImage
     */
    public function makeClickImage(): ClickImage
    {
        $data = new ClickData();
        $image = new ClickImage();
        $this->setCommon($image, $data);
        $this->setClick($image, $data);
        return $image;
    }

    /**
     * 设置公共配置
     * @access protected
     * @param BaseImage $image
     * @param BaseData $data
     * @return void
     */
    protected  function setCommon(BaseImage $image, BaseData $data)
    {
        //固定驱动，少量图片处理场景gd性能远远大于imagick
        ImageManagerStatic::configure(['driver' => 'gd']);

        //获得字体数据
        $fontFile = $data->getFontFile($this->options['font_file']);
        $image->setFontFile($fontFile)->setWatermark([
            'text' => $this->options['watermark_text'],
            'fontsize' => $this->options['watermark_fontsize'],
            'color' => $this->options['watermark_color'],
        ]);
    }

    /**
     * 设置滑动验证码的配置
     * @access protected
     * @param SlideImage $image
     * @param SlideData $data
     * @return void
     */
    protected  function setSlide(SlideImage $image, SlideData $data)
    {
        //设置背景
        $backgroundVo = $data->getBackgroundVo($this->options['backgrounds']);
        $image->setBackgroundVo($backgroundVo);

        $templateVo = $data->getTemplateVo($backgroundVo, $this->options['slide_templates']);

        $interfereVo = $data->getInterfereVo($backgroundVo, $templateVo, $this->options['slide_templates']);

        if(true === $this->options['slide_cache_pixel']){
            $cache = $this->getCacheInstance();
            foreach ([$backgroundVo, $templateVo, $interfereVo] as $vo){
                /**@var ImageVo $vo**/
                $key = 'image_pixel_map_'.$vo->src;
                $result = $cache->get($key);
                if(!empty($result) && is_array($result)){
                    $vo->setPickMaps($result);
                }else{
                    $vo->preparePickMaps();
                    $vo->setFinishCallback(function(ImageVo $imageVo)use($cache, $key){
                        $cache->set($key, $imageVo->getPickMaps(),0);
                    });
                }
            }
        }

        $image->setTemplateVo($templateVo)->setInterfereVo($interfereVo);
    }

    /**
     * 设置文字验证码的配置
     * @access protected
     * @param ClickImage $image
     * @param ClickData $data
     * @return void
     */
    protected function setClick(ClickImage $image, ClickData $data)
    {
        //设置背景
        $backgroundVo = $data->getBackgroundVo($this->options['backgrounds']);
        $image->setBackgroundVo($backgroundVo);
        // 宽度
        $width = $image->getBackgroundVo()->image->getWidth();
        // 高度
        $height = $image->getBackgroundVo()->image->getHeight();
        // 文字数量
        $length = $this->options['click_word_length'];
        //随机文字坐标
        $pointList = $data->getPointList($width, $height, $length);
        // 获取文字列表
        $worldList = $data->getWordList(count($pointList));
        // 设置文字
        $image->setWordList($worldList)->setPoint($pointList);
    }

    /**
     * 创建缓存实体
     * @access public
     * @return Cache
     */
    public function getCacheInstance(): Cache
    {
        if(empty($this->cacheInstance)){
            $this->cacheInstance = new Cache([
                'handler' => $this->options['cache_handler'],
                'method_map' => $this->options['cache_method_map'],
                'options' => $this->options['cache_options'],
            ]);
        }
        return $this->cacheInstance;
    }

    /**
     * 生成文字内容
     * @access public
     * @return ClickData
     */
    public function makeClickData(): ClickData
    {
        return new ClickData();
    }

    /**
     * 生成图片内容
     * @access public
     * @return SlideData
     */
    public function makeSlideData(): SlideData
    {
        return (new SlideData())->setFaultOffset($this->options['slide_offset']);
    }

    /**
     * 获取配置参数
     * @access public
     * @param string $name
     * @return mixed
     */
    public function getConfig($name = null)
    {
        // 不存在
        if(empty($name) || !isset($this->options[$name])){
            return null;
        }
        // 返回
        return $this->options[$name];
    }
}
