<?php
namespace app\mobile\controller;

use think\Controller;
use think\Session;
use app\base\service;
use app\mobile\service\UserService;

class Base extends Controller
{

    public function _initialize()
    {
        Session::start();
        header("Cache-control: private");  // history.back返回后输入框值丢失问题 参考文章 http://www.tp-shop.cn/article_id_1465.html  http://blog.csdn.net/qinchaoguang123456/article/details/29852881
        $this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID', $this->session_id); //将当前的session_id保存为常量，供其它方法调用
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            define('IS_GET', 0);
            define('IS_POST', 1);
        } else {
            define('IS_GET', 1);
            define('IS_POST', 0);
        }

        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $conf = include(APP_PATH.'base/conf/conf.php');
            $this->weixin_config = $conf['wx'];
            $jssdk = new service\Jssdk($this->weixin_config['appid'], $this->weixin_config['appsecret']);
            $jssdk->sendOnceTemplate($_SESSION['openid'], "黑店", '电影票');
            if (empty($_SESSION['openid'])) {
                $wxuser = $jssdk->GetOpenid(); //授权获取openid以及微信用户信息
                //微信自动登录
                $logic = new UserService();
                $data = $logic->thirdLogin($wxuser);

                if($data['status'] == 1) {
                    session('user', $data['result'], null);
                }
            }
            $signPackage = $jssdk->GetSignPackage();

            $this->assign('root_path', PROJECT_PATH);
            $this->assign('wx_share_path', PROJECT_PATH."/public/base/wx_share.html");
            $this->signPackage = $signPackage;
            $this->signPackage['img'] = '';
            $this->signPackage['link'] = '/mobile/index/index';
            $this->signPackage['desc'] = '优惠券大放送';
            $this->signPackage['title'] =  '优惠券大放送';
            $this->assign('signPackage', $this->signPackage);
        }
        
        $msg_count = 0;
        if(isset($_SESSION['user'])) {
            $query = array(
                'uid' => $_SESSION['user']['id'],
                'deleted' => 0,
                'is_read' => 0,
            );
            $msg_count = M('msg')->where($query)->count();
            $this->assign('number', $_SESSION['user']['id']);
        }
        $this->assign('msg_count', $msg_count);
    }

    public function get_website() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $url = $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
        return $url;
    }

    public function setOpenid($user) {
        if(isset($user) && !$user['openid'] && isset($_SESSION['openid'])) {
            $set = array(
                'openid' => $_SESSION['openid'],
            );
            M('user')->where('id='.$user['id'])->update($set);
        }
    }
    public function ajaxReturn($data){
        Header('Content-type:application/json; charset=UTF-8');
        if(is_string($data)) {
            $data = array(
                'msg'=> $data,
            );
        }
        exit(json_encode($data));
    }

    public function get_util() {
        $util = new \app\base\service\Util();
        return $util;
    }















}
