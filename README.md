# AJ-Captcha SDK for PHP

一个基于PHP的AJ-Captcha SDK


## 安装
~~~
composer require axguowen/ajcaptcha-sdk-php
~~~

## 用法示例

获取验证码

~~~php

use axguowen\Ajcaptcha;

// 配置
$config = [
    // 验证码类型, slide滑动验证, click点选验证
    'verify_type' => 'click',
];
// 实例化
$ajcaptcha = new Ajcaptcha($config);

// 获取验证码
$getcaptcha = $ajcaptcha->get();

var_dump($getcaptcha);

~~~


一次验证

~~~php

use axguowen\Ajcaptcha;

// 配置
$config = [
    // 验证码类型, slide滑动验证, click点选验证
    'verify_type' => 'click',
];
// 实例化
$ajcaptcha = new Ajcaptcha($config);
// 一次验证
$checkResult = $ajcaptcha->check($token, $pointJson);
// 失败
if(is_null($checkResult[0])){
    throw $checkResult[1];
}
// 成功
var_dump($checkResult[0]);

~~~


二次验证

~~~php

use axguowen\Ajcaptcha;

// 配置
$config = [
    // 验证码类型, slide滑动验证, click点选验证
    'verify_type' => 'click',
];
// 实例化
$ajcaptcha = new Ajcaptcha($config);
// 二次验证
$validateResult = $ajcaptcha->validate($encryptCode, $token, $pointJson);
// 失败
if(is_null($validateResult[0])){
    throw $validateResult[1];
}
// 成功
var_dump($validateResult[0]);

~~~

