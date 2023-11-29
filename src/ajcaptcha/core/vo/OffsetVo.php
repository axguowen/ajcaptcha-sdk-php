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

class OffsetVo
{
    /**
     * X坐标
     * @var int
     */
    public $x;

    /**
     * Y坐标
     * @var int
     */
    public $y;

    /**
     * 构造方法
     * @access public
     * @param int $x
     * @param int $y
     * @return void
     */
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

}
