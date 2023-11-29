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

use Intervention\Image\Image;

class TemplateVo extends ImageVo
{
    /**
     * 偏移量
     * @var OffsetVo
     */
    public $offset;

    /**
     * 获取偏移量
     * @access public
     * @return OffsetVo
     */
    public function getOffset(): OffsetVo
    {
        return $this->offset;
    }

    /**
     * 设置偏移量
     * @access public
     * @param OffsetVo $offset
     */
    public function setOffset(OffsetVo $offset): void
    {
        $this->offset = $offset;
    }



}
