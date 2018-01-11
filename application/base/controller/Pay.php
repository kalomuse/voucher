<?php
namespace app\base\controller;
use think\Controller;
use think\Db;
use think\Session;
use app\mobile\service\OrderService;
use app\base\service;
require_once (PROJECT_PATH.'plugins/weixin/weixin.class.php');
class Pay extends Controller
{
    public function Index() {
        Session::start();
        if(!isset($_SESSION['openid'])) {
            exit('请在微信客户端进行支付');
        }
        if(!isset($_SESSION['user'])) {
            return array('status'=>'fail', 'msg'=> '请先登录');
        }
        $id = I('id');
        $voucher = M('buy_voucher')->where('id', $id)->find();
        $count = M('buy_join')->where('aid', $voucher['id'])->count();
        if($voucher['total'] <= $count) {
            return $this->ajaxReturn('优惠券已发放完毕，谢谢参与');
        } else if(strtotime($voucher['end_time']) <= time()) {
            return $this->ajaxReturn('本次活动已结束，谢谢参与');
        }

        $price = $voucher['price'];
        if($price == 0) {
            $query = array(
                'aid' => $id,
                'uid' => $_SESSION['user']['id'],
            );
            $res = M('buy_join')->where($query)->find();
            if(!$res) {
                $set = array(
                    'aid' => $id,
                    'uid' => $_SESSION['user']['id'],
                    'pwd' => $this->randStr()
                );
                M('buy_join')->insert($set);
            }
            header("Location: ".SITE_URL."/mobile/index/mine?id=0");
        }
        $user = M('user')->where("id={$_SESSION['user']['id']}")->find();

        //嘉善圈授权
        if(!$user['money_openid']) {
            if (strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
                $conf = include(APP_PATH . 'base/conf/conf.php');
                $this->weixin_config = $conf['wx_pay'];
                $jssdk = new service\Jssdk($this->weixin_config['appid'], $this->weixin_config['appsecret']);
                $openid = $jssdk->GetOpenid('pay');
                $set = array(
                    'money_openid' => $openid
                );
                M('user')->where("id={$_SESSION['user']['id']}")->update($set);
            } else {
                return array('status'=>'fail', 'msg'=> '支付只能在微信客服端进行');
            }
        }

        $code = '\\weixin';
        $wexin = new $code();

        $t = time();
        $rand = rand(1000, 9999);
        $order = array(
            'order_id' => strval($t) . strval($rand),
            'order_sn' => 'i'.strval($t) . strval($rand),
            'order_amount' => $voucher['price'],
        );

        $notify_url = '/base/pay/callback';
        $go_url = "/mobile/index/mine?id=2";
        $back_url = $_SERVER['HTTP_REFERER'];
        $res = $wexin->getJSAPI($order, $go_url, $back_url, $notify_url);

        //生成订单
        $order = array(
            'order_sn' => $order['order_sn'],
            'user_id' => $_SESSION['user']['id'],
            'aid' => $id,
            'order_amount' => $price,
            'type' => 1,
            'is_get' => 1,
            'goods_price' => $price,
            'goods_num' => 1,
            'total_amount' => $voucher['old_price'],
            'add_time' => time()
        );
        $order_service = new OrderService();
        $order_service->add_order($order);
        exit($res);
    }
    public function Callback() {
        $code = '\\weixin';
        $wexin = new $code();
        $wexin->response();
    }
    public function Pack() {
        Session::start();
        if(!isset($_SESSION['user'])) {
            return array('status'=>'fail', 'msg'=> '请先登录');
        }
        $user = M('user')->where("id={$_SESSION['user']['id']}")->find();
        //嘉善圈授权
        if(!$user['money_openid']) {
            if (strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
                $conf = include(APP_PATH . 'base/conf/conf.php');
                $this->weixin_config = $conf['wx_pay'];
                $jssdk = new service\Jssdk($this->weixin_config['appid'], $this->weixin_config['appsecret']);
                $openid = $jssdk->GetOpenid('pay');
                $set = array(
                    'money_openid' => $openid
                );
                M('user')->where("id={$_SESSION['user']['id']}")->update($set);
            } else {
                return array('status'=>'fail', 'msg'=> '红包发放只能在微信客服端进行');
            }
        }
        $user['file'] = explode('||', $user['file'])[0];
        $this->assign('my', $user);

        return $this->fetch('');
    }
    public function get_money() {
        Session::start();
        if(!isset($_SESSION['user'])) {
            return array('status'=>'fail', 'msg'=> '请先登录');
        }
        $user = M('user')->where("id={$_SESSION['user']['id']}")->find();
        if(!$user['money_openid']) {
            return array('status'=>'fail', 'msg'=> '未知的错误');
        }

        include_once(PROJECT_PATH . 'plugins/weixin/weixin.class.php');

        if(!(isset($user) && $user))
            return array('status'=>'fail', 'msg'=> '用户未登录或账号异常');
        else if($user['money'] <= 0)
            return array('status'=>'fail', 'msg'=> '账户余额不足');

        $amount = M('bill')->where("userid={$_SESSION['user']['id']} and is_get=0 and has_pay=0 and deleted=0")->sum('amount');
        if($amount == $user['money']) { //账单校验
            $code = '\\weixin';
            $wexin = new $code();
            $res = $wexin->redpack($user['money_openid'], $user['money']);
            if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
                $set = array(
                    'money' => 0,
                );
                M('user')->where("id={$_SESSION['user']['id']}")->update($set);
                $set = array(
                    'has_pay' => 1,
                    'modified_time' => date("Y-m-d H:i:s"),
                );
                M('bill')->where("userid={$_SESSION['user']['id']} and is_get=0 and has_pay=0 and deleted=0")->update($set);
                $set = array(
                    'uid' => $user['id'],
                    'type' => 'REDPACK_SUCCESS',
                    'content' => "提现成功，提现金额{$amount}元",
                );
                M('msg')->insert($set);
                return array('status'=>'ok');
            } else { //发放失败记录信息
                $set = array(
                    'uid' => $user['id'],
                    'type' => 'REDPACK_FAIL',
                    'content' => "提现：".$res['return_msg']. "，请联系客服",
                );
                M('msg')->insert($set);
            }
        } else {
            $set = array(
                'uid' => $user['id'],
                'type' => 'REDPACK_FAIL',
                'content' => '提现：用户金额异常，请联系客服',
            );
            M('msg')->insert($set);
        }
        return array('status'=>'fail', 'msg'=> $set['content']);

    }
    public function Redpack() {
        exit();
        Session::start();

        if(!isset($_SESSION['openid'])) {
            exit('请在微信客户端进行支付');
        }

        include_once(PROJECT_PATH . 'plugins/weixin/weixin.class.php');
        $code = '\\weixin';
        $wexin = new $code();
        $money = C('invite_money');
        $res = $wexin->redpack($leader['openid'], $money);
        if($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
            //账单生成
            $set = array(
                'is_get' => 0,
                'type' => 101,
                'amount' => $res['total_amount'] / 100,
                'openid' => $res['re_openid'],
            );
            M('bill')->insert($set);
            return true;
        } else { //发放失败记录, 记录msg
            $set = array(
                'uid' => $id,
                'type' => 'REDPACK_FAIL',
                'content' => $res['return_msg'],
            );
            M('msg')->insert($set);
        }
    }
    function randStr($len=6,$format='') {
        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        mt_srand((double)microtime()*1000000*getmypid());
        $password="";
        while(strlen($password)<$len)
            $password.=substr($chars,(mt_rand()%strlen($chars)),1);
        return $password;
    }

}
