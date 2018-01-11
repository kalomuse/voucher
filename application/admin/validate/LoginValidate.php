<?php
namespace app\admin\validate;

use think\Validate;

class LoginValidate extends Validate
{
    protected $rule = [
        'username'  =>  'require|max:25',
        'password|密码' =>  'require|max:32|min:6',
        //'verify' => 'require|captcha'
    ];

}