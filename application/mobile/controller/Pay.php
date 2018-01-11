<?php

namespace app\mobile\controller;

use think\Page;
use think\Request;
use think\Verify;
use think\db;
use app\mobile\service\UserService;

class Pay extends Base
{
    public function rule() {
        $type = I('type', '');
        $this->assign('type', $type);
        $vip = C('vip');
        $this->assign('vip', $vip);
        return $this->fetch();
    }
    public function choice() {
        $my = M('user')->where('id='.$_SESSION['user']['id'])->find();
        $vip = C('vip');
        if($my['level'] == 1 || $my['level'] == 2) { //一次性套餐升级年费套餐
            foreach ($vip as $k => &$v) {
                if($k != 1 && $k != 2) {
                    $v['money'] -= $vip[$my['level']]['money'];
                }
                $v['text'] = str_replace('{{money}}', $v['money'], $v['text']);
            }
        } else if($my['level'] > 2){ //低等级年费套餐升级高等级年费套餐
            $money = intval($vip[$my['level']]['money'] * ($my['vip_time'] - time()) / (24*3600*365));
            foreach ($vip as $k => &$v) {
                if($k != 1 && $k != 2 && $k > $my['level']) {
                    $v['money'] -= $money;
                } else if($k != 1 && $k != 2 && $k <= $my['level']) {
                    unset($vip[$k]);
                }
                $v['text'] = str_replace('{{money}}', $v['money'], $v['text']);
            }
        } else { //如果不是vip显示原来价格
            foreach ($vip as $k => &$v) {
                $v['text'] = str_replace('{{money}}', $v['money'], $v['text']);
            }
        }

        $this->assign('level', $my['level']);
        $this->assign('vip', $vip);
        $this->assign('type', I('type', ''));
        return $this->fetch();
    }

    public function invite() {
        $type = I('type', '');
        $my = M('user')->where('id='.$_SESSION['user']['id'])->find();
        $my['file'] = explode('||', $my['file'])[0];
        $this->assign('my', $my);
        $this->assign('type', $type);
        $this->assign('invite_money', C('invite_money'));

        return $this->fetch();
    }
    public function accept() {
        $code = I('f', '');
        $code = base64_decode($code);
        $time = substr($code, 0, 10);
        $now_time = time();
        $type = I('type', '');
        if($now_time - $time > 3600 *24)
            $this->error('邀请码已过期');
        $leader_uid = substr($code, 10);
        session('leader', $leader_uid);
        if($type == 'distribut')
            session('is_distribut_reg', $type);
        $his = M('user')->where('id='.$leader_uid)->find();
        $his['file'] = explode('||', $his['file'])[0];
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $this->assign('is_wx', 1);
        } else {
            $this->assign('is_wx', 0);
        }
        $this->assign('code', I('f', ''));
        $this->assign('his', $his);

        return $this->fetch();
    }
    public function invite_code() {
        $uid = $_SESSION['user']['id'];
        $time = time();
        $code = base64_encode(strval($time).strval($uid));
        $type = I('type', '');
        $util = $this->get_util();
        $code = $util->get_website().'/mobile/pay/accept/f/' . $code."?type=$type";
        $code = $this->get_util()->qrcode($uid, $code);
        return $this->ajaxReturn(array('status'=> 1, 'code'=>$code));
    }

}