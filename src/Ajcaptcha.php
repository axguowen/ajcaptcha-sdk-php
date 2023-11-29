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

namespace axguowen;

use axguowen\ajcaptcha\service\SlideService;
use axguowen\ajcaptcha\service\ClickService;
use axguowen\ajcaptcha\exception\ParamException;
use axguowen\ajcaptcha\utils\AesUtils;

class Ajcaptcha
{
    /**
     * 配置
     * @var array
     */
    protected $options = [
        // 验证码类型, slide滑动验证, click点选验证
        'verify_type' => 'click',
        // 自定义字体包路径, 不填使用默认值
        'font_file' => '',
        // 背景图片路径, 不填使用默认值, 支持string与array两种数据结构, string为默认图片的目录, array索引数组则为具体图片的地址
        'backgrounds' => [],
        // 点击验证码文字数量
        'click_word_length' => 5,
        // 点击验证码验证文字数量
        'click_check_length' => 4,
        // 滑动验证码模板图, 格式同上支持string与array
        'slide_templates' => [],
        // 滑动验证码容错偏移量
        'slide_offset' => 10,
        // 是否开启缓存图片像素值，开启后能提升服务端响应性能（但要注意更换图片时，需要清除缓存）
        'slide_cache_pixel' => true, 
        // 水印文字内容
        'watermark_text' => '',
        // 水印文字大小
        'watermark_fontsize' => 12,
        // 水印文字颜色
        'watermark_color' => '#ffffff',
        // 缓存驱动类
        'cache_handler' => '',
        // 缓存方法映射
        'cache_method_map' => [],
        // 缓存驱动配置参数
        'cache_options' => [],
    ];

    /**
     * 验证码服务
     * @var mixed
     */
    protected $service;

    /**
     * 构造方法
     * @access public
     * @param array $config
     * @return void
     */
    public function __construct($config = [])
    {
        // 合并配置
        $this->options = array_merge($this->options, $config);
        // 如果是滑动验证
        if($this->options['verify_type'] == 'slide'){
            $this->service = new SlideService($this->options);
        }
        // 如果是点选验证
        elseif($this->options['verify_type'] == 'click'){
            $this->service = new ClickService($this->options);
        }
        else{
            throw new \Exception('验证类型配置错误');
        }
    }

    /**
     * 获取验证码
     * @access public
     * @return array
     */
    public function get()
    {
        return $this->service->get();
    }

    /**
     * 一次验证
     * @access public
     * @param string $encryptCode
     * @param string $token
     * @param string $pointJson
     * @return void
     */
    public function check($token, $pointJson)
    {
        return $this->service->check($token, $pointJson);
    }

    /**
     * 二次验证
     * @access public
     * @param string $token
     * @param string $pointJson
     * @return void
     */
    public function validate($encryptCode, $token, $pointJson)
    {
        return $this->service->validate($encryptCode, $token, $pointJson);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->service, $method], $args);
    }
}
