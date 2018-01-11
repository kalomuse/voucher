<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/6/12
 * Time: 上午9:18
 */
return array(
    array('str'=>'店铺加盟', 'name'=>'shop', 'icon'=>'fa-desktop', 'child'=>array(
        array('str'=>'列表', 'action'=>'index'),
    )),
    array('str'=>'抽奖优惠券管理', 'name'=>'randvoucher', 'icon'=>'fa-desktop', 'child'=>array(
        array('str'=>'抽奖优惠券发放', 'action'=>'rand'),
    )),
    array('str'=>'购买优惠券管理', 'name'=>'buyvoucher', 'icon'=>'fa-desktop', 'child'=>array(
        array('str'=>'购买优惠券发放', 'action'=>'buy'),
    )),
    array('str'=>'账单管理', 'name'=>'bill', 'icon'=>'fa-desktop', 'child'=>array(
        array('str'=>'查询', 'action'=>'list'),
    )),
    array('str'=>'权限', 'name'=>'role', 'icon'=>'fa-desktop', 'child'=>array(
        array('str'=>'管理员列表', 'action'=>'list'),
        array('str'=>'角色分配', 'action'=>'to'),
    )),
);

