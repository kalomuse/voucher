<?php
namespace app\mobile\service;
use think\Db;
require_once APP_PATH . "base/service/Util.php";
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/7/8
 * Time: 下午3:39
 */
class OrderService
{

    function add_order($order) {
        $order['user_id'] = $_SESSION['user']['id'];
        $order['mobile'] = $_SESSION['user']['mobile'];
        $order['pay_status'] = 0;
        $order['add_time'] = time();
        $order['pay_name'] = 'weixin';
        M('orders')->insert($order);
        return true;

    }
    function pay_order($order) {
        //刷新订单
        $set = array(
            'pay_status' => 1,
            'pay_time' => time(),
            'transaction_id' => $order['transaction_id']
        );
        M('orders')->where("order_sn=\"{$order['out_trade_no']}\"")->update($set);

        $order = M('orders')->where("order_sn=\"{$order['out_trade_no']}\"")->find();
        $set = array(
            'aid' => $order['aid'],
            'uid' => $order['user_id'],
            'pwd' => $this->randStr()
        );
        M('buy_join')->insert($set);

        //生成账单
        $set = array(
            'is_get' => $order['is_get'],
            'type' => $order['type'],
            'amount' => $order['order_amount'],
            'order_id' => $order['id'],
        );
        M('bill')->insert($set);

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