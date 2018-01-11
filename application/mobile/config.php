<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/7/23
 * Time: 下午2:08
 */
 return [
    'template' => [
       'view_path'    => '',
       'layout_on'     =>  false,
       'layout_name'   =>  'layout',
      ],
       'view_replace_str'  =>  [
            '__STATIC__' => '/application/mobile/view/static',
            '__IMG__' => '/public/img/local/'
       ],
     'qrcode'=> '/public/img/local/wechat.jpg',
     'title' => 'V.PUB咖啡音乐餐吧邀你来嗨', //页面标题
     'desc' => 'V.PUB咖啡音乐餐吧邀你来嗨', //分享描述
     'link' => 'http://wechat.tikyy.com/vpub',
     'img' => 'http://wechat.tikyy.com/application/vpub/public/img/icon.jpg'
 ];