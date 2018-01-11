<?php

namespace app\mobile\controller;

use think\Page;
use think\Verify;
use app\mobile\validate\UserValidate;
use app\base\service\Util;

class User extends Base
{

    public $user_id = 0;
    public $user = array();
    public $orderLogic; // 订单逻辑操作类

    public function wechat() {
        $qrcode = C('qrcode');
        $this->assign('qrcode', $qrcode);
        return $this->fetch();
    }

    public function readMsg() {
        $query = array(
            'uid' => $_SESSION['user']['id'],
            'deleted' => 0,
            'is_read' => 0,
        );
        $set = array(
            'is_read' => 1,
        );
        M('msg')->where($query)->update($set);
        return true;
    }

    public function msg() {
        $query = array(
            'uid' => $_SESSION['user']['id'],
            'deleted' => 0,
        );
        $msgs = M('msg')->where($query)->order('id desc')->select();
        foreach($msgs as &$m) {
            if($type ='LIKE') {
                $query = array(
                    'id' => $m['other']
                );
                $his = M('user')->where($query)->find();
                $his['file'] = explode('||', $his['file'])[0];
                $m['his'] = $his;
            }
        }
        $this->assign('type', 'msg');
        $this->assign('msgs', $msgs);
        return $this->fetch();
    }

    public function index() {
        $user = M('user')->where('id='.$_SESSION['user']['id'])->find();
        $this->setOpenid($user);
        $user['file'] = explode('||', $user['file'])[0];
        if($user['status'] == 1) {
            header("location: /mobile/user/step1?type=reg");
        }
        $this->assign('type', 'center');
        $this->assign('user', $user);
        return $this->fetch('center');
    }

    public function center() {
        $user = M('user')->where('id='.$_SESSION['user']['id'])->find();
        $this->setOpenid($user);
        $user['file'] = explode('||', $user['file'])[0];
        if($user['status'] == 1) {
            header("location: /mobile/user/step1?type=reg");
        }
        $this->assign('type', 'center');
        $this->assign('user', $user);
        return $this->fetch();
    }

    public function rule() {
        return $this->fetch();
    }

    public function step1() {
        if(IS_GET) {
            $user = M('user')->where('id='.$_SESSION['user']['id'])->find();
            $user['file'] = explode('||', $user['file'])[0];
            $type = I('type', '');
            $redirect = I('redirect', '');
            $invite_code = I('code', '');
            $this->assign('from', I('from', ''));
            $this->assign('invite_code', $invite_code);
            $this->assign('redirect', $redirect);
            $this->assign('type', $type);
            $this->assign('user', $user);
            $this->assign('id', $_SESSION['user']['id']);
            return $this->fetch();
        }
        $set = array(
            'car' => I('car', ''),
            'name' => I('name', ''),
            'birth' => I('birth', ''),
            'birth_time' => I('birth_time', ''),
            'status' => 2,
        );

        $validate = new UserValidate();
        // 实例化Login对象
        if (!$validate->check($set)) {
            $err = $validate->getError();
            return $this->ajaxReturn($err);
        }
        if(!$_SESSION['user']['mobile']) {
            $is_validated = 0;
            if (check_mobile(I('mobile', ''))) {
                $is_validated = 1;
            }

            if ($is_validated != 1)
                return array('status' => -1, 'msg' => '请用手机号注册');

            //验证是否存在用户名
            $query = array(
                'mobile' => I('mobile')
            );
            $user = M('user')->where($query)->find();
            if ($user)
                return array('status' => -1, 'msg' => '账号已存在');

            $code = I('invite_code', '');
            if($code) {
                $code = base64_decode($code);
                $leader_uid = substr($code, 10);
                if($leader_uid) {
                    $set['leader'] = $leader_uid;
                }
            }
            //邀请码
            $code = I('invite_code', '');
            if($code) {
                $code = base64_decode($code);
                $leader_uid = substr($code, 10);
                if($leader_uid) {
                    $set['leader'] = $leader_uid;
                    if(isset($_SESSION['is_distribut_reg']) &&  $_SESSION['is_distribut_reg'] == 'distribut')
                        $set['role'] = 'distribut';
                }
            }
            $set['mobile'] = $_POST['mobile'];
        }

        M('user')->where('id='.$_SESSION['user']['id'])->update($set);
        $user = M('user')->where('id='.$_SESSION['user']['id'])->find();
        session('user', $user);

        $this->ajaxReturn(array('status'=>'ok'));

    }


    private function calcAge($birthday) {
        $iage = 0;
        if (!empty($birthday)) {
            $year = date('Y',strtotime($birthday));
            $month = date('m',strtotime($birthday));
            $day = date('d',strtotime($birthday));

            $now_year = date('Y');
            $now_month = date('m');
            $now_day = date('d');

            if ($now_year > $year) {
                $iage = $now_year - $year - 1;
                if ($now_month > $month) {
                    $iage++;
                } else if ($now_month == $month) {
                    if ($now_day >= $day) {
                        $iage++;
                    }
                }
            }
        }
        return $iage;
    }


}