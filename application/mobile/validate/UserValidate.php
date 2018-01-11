<?php
namespace app\mobile\validate;

use think\Validate;



class UserValidate extends Validate
{
    protected $rule = [
        'name' => 'require',
        'contact' => 'require',
        'address' => 'require',
    ];

}