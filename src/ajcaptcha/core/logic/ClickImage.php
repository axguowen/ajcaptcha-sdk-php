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

use axguowen\ajcaptcha\core\vo\PointVo;
use Intervention\Image\ImageManagerStatic as ImageManager;
use Intervention\Image\AbstractFont as Font;
use axguowen\ajcaptcha\utils\RandomUtils;

/**
 * 文字码图片处理
 */
class ClickImage extends BaseImage
{

    /**
     * 文字列表
     * @var array
     */
    protected $wordList;

    /**
     * 设置文字
     * @access public
     * @param array $wordList
     * @return $this
     */
    public function setWordList(array $wordList)
    {
        $this->wordList = $wordList;
        return $this;
    }

    /**
     * 获取文字
     * @access public
     * @return array
     */
    public function getWordList()
    {
        return $this->wordList;
    }
    
    /**
     * 运行
     * @access public
     * @return void
     */
    public function run()
    {
        $this->inputWords();
        $this->makeWatermark($this->backgroundVo->image);
    }

    /**
     * 写入文字
     * @access protected
     * @return void
     */
    protected function inputWords(){
        foreach ($this->wordList as $key => $word) {
            $point = $this->point[$key];
            $this->backgroundVo->image->text($word, $point->x, $point->y, function (Font $font) {
                $font->file($this->fontFile);
                $font->size(BaseData::FONTSIZE);
                $font->color(RandomUtils::getRandomColor());
                $font->angle(RandomUtils::getRandomAngle());
                $font->align('center');
                $font->valign('center');
            });
        }
    }

    /**
     * 返回前端需要的格式
     * @access public
     * @return false|string
     */
    public function response()
    {
        $result = $this->getBackgroundVo()->image->encode('data-url')->getEncoded();
        //返回图片base64的第二部分
        return explode(',', $result)[1];
    }

    /**
     * 用来调试
     * @access public
     * @return void
     */
    public function echo()
    {
        die($this->getBackgroundVo()->image->response());
    }
}
