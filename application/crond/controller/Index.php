<?php
namespace app\crond\controller;

use think\console\command\make\Controller;
use app\base\service\Jssdk;

class Index extends Controller
{
    private $tableService;

    public function rand()
    {
        $voucher = M('rand_voucher')->select();
        $conf = include(APP_PATH.'base/conf/conf.php');
        $this->weixin_config = $conf['wx'];
        $jssdk = new Jssdk($this->weixin_config['appid'], $this->weixin_config['appsecret']);

        foreach ($voucher as $v) {
            if (substr($v['end_time'], 0, 10) == date("Y-m-d", intval(time())) && !$v['is_end']) {
                $id = $v['id'];
                $join = M('join')->where('aid', $id)->select();
                $jid = array();
                foreach ($join as $j) {
                    $jid[$j['id']] = $j['uid'];
                }

                $rand = array_rand($jid,  count($jid) < $v['total']?count($jid): $v['total']);
                foreach ($rand as $r) {
                    $pwd = $this->randStr();
                    $set = array(
                        'status'=> 2,
                        'pwd' => $pwd
                    );

                    M('join')->where('id', $r)->update($set);
                    $user = M('user')->where('id', $jid[$r])->find();
                    if($user)
                        $jssdk->sendTemplate($user['openid'], $v['shop'], $v['item'], $pwd);
                }
                $query = array(
                    'aid' => $id,
                    'id' => array('not in', implode(',', $rand))
                );
                M('join')->where($query)->update(array('status'=> 3));
                M('rand_voucher')->where('id', $v['id'])->update(array('is_end'=>1));
                echo "开奖成功";
            }
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