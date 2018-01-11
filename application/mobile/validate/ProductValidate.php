<?php
namespace app\mobile\validate;

use think\Validate;



class ProductValidate extends Validate
{
    protected $rule = [
        ['title', 'require', '标题不能为空'],
        ['rule' , 'require', '规则不能为空'],
        ['count', 'require|min:1', '抢购人数必须大于0'],
        ['number', 'require|min:1', '库存必须大于0'],
        ['low_price', 'require|gt:0', '底价不能为空'],
        ['old_price', 'require|gt:low_price', '原价必须大于底价'],
        ['start_time', 'require', '开始时间不能为空'],
        ['end_time', 'require|gt:start_time', '结束时间必须大于开始时间'],
        ['desc', 'require', '商品描述不能为空'],
        ['first_pic', 'require', '活动展示图不能为空'],
        ['pic', 'require', '商品图不能为空'],
    ];

}