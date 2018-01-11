<?php

namespace app\mobile\controller;

use think\Page;
use think\Request;
use think\Session;
use think\Verify;
use think\db;
use app\mobile\service\UserService;
use app\base\service;

class Login extends Base
{

    public $id = 0;
    public $user = array();
    public $orderLogic; // 订单逻辑操作类

    /*
    * 初始化操作
    */
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
    }

    /*
     * 用户中心首页
     */
    public function index() {
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Mobile/User/index");
        $this->assign('referurl', $referurl);
        return $this->fetch();
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        setcookie('cn', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');
        //$this->success("退出成功",U('Mobile/Index/index'));
        header("Location:" . U('/'));
        exit();
    }

    public function issetMobile()
    {
        $mobile = I("mobile",'0');
        $users = M('user')->where('mobile',$mobile)->find();
        if($users)
            exit ('1');
        else
            exit ('0');
    }

    /**
     * app login
     */
    public function third_login() {
        $logic = new UserService();
        $res = $logic->thirdLogin($_POST);
        if(isset($res['result'])) {
            $data = array(
                'status' => 1,
                'id'=> $res['result']['id'],
                'uname'=> empty($res['result']['nickname']) ? $res['result']['mobile'] : $res['result']['nickname'],
                'mobile'=> $res['result']['mobile'],
                'token'=> md5(C('salt').$res['result']['id']),
            );
            $util = new service\Util();
            $util->redis->setex('i_'.$data['token'], 30*24*3600, $res['result']['id']);
            return ajaxReturn($data);
        } else {
            return ajaxReturn($res);
        }


    }

    /*
     * 登录
     */
    public function do_login() {
        $username = I('post.username');
        $password = I('post.password', 0);
        $username = trim($username);
        $password = trim($password);

        $logic = new UserService();
        $res = $logic->do_login($username, $password);
        if ($res['status'] == 1) {
            $data = array(
                'status' => 1,
                'url' => urldecode(I('post.referurl')),
                'id'=> $res['result']['id'],
                'number'=> $res['result']['number'],
                'sex'=> $res['result']['sex'],
                'mobile'=> $res['result']['mobile'],
                'token'=> md5(C('salt').$res['result']['id']),
            );

            session('user', $data, null);
            exit(json_encode($data));
        }
        exit(json_encode($res));
    }

    public function rule() {
        return $this->fetch();
    }

    /**
     *  注册
     */
    public function reg()
    {
        if(IS_GET) {
            $this->assign('code', I('code', ''));
            return $this->fetch();
        }
        $logic = new UserService();
        //验证码检验
        $util = new service\Util();
        $type = I('post.type', '');
        $res = $util->check_validate_code($_POST['mobile_code'], $_POST['username'], $type);
        if ($res['status'] != 1)
            $this->ajaxReturn($res);
        $username = I('post.username', '');
        $password = I('post.password', '');
        $password2 = I('post.password2', '');

        $res = $logic->reg($username, $password, $password2);
        if($res['status'] != 1)
            $this->ajaxReturn($res);

        session('user', $res['result']);
        return $this->ajaxReturn(array('status' => 1));
    }



    /*
     * 邮箱验证
     */
    public function email_validate()
    {
        $userLogic = new UserService();
        $user_info = $userLogic->get_info($this->id); // 获取用户信息
        $user_info = $user_info['result'];
        $step = I('get.step', 1);
        //验证是否未绑定过
        if ($user_info['email_validated'] == 0)
            $step = 2;
        //原邮箱验证是否通过
        if ($user_info['email_validated'] == 1 && session('email_step1') == 1)
            $step = 2;
        if ($user_info['email_validated'] == 1 && session('email_step1') != 1)
            $step = 1;
        if (IS_POST) {
            $email = I('post.email');
            $code = I('post.code');
            $info = session('email_code');
            if (!$info)
                $this->error('非法操作');
            if ($info['email'] == $email || $info['code'] == $code) {
                if ($user_info['email_validated'] == 0 || session('email_step1') == 1) {
                    session('email_code', null);
                    session('email_step1', null);
                    if (!$userLogic->update_email_mobile($email, $this->id))
                        $this->error('邮箱已存在');
                    $this->success('绑定成功', U('Home/User/index'));
                } else {
                    session('email_code', null);
                    session('email_step1', 1);
                    redirect(U('Home/User/email_validate', array('step' => 2)));
                }
                exit;
            }
            $this->error('验证码邮箱不匹配');
        }
        $this->assign('step', $step);
        return $this->fetch();
    }

    /*
    * 手机验证
    */
    public function mobile_validate()
    {
        $userLogic = new UserService();
        $user_info = $userLogic->get_info($this->id); // 获取用户信息
        $user_info = $user_info['result'];
        $step = I('get.step', 1);
        //验证是否未绑定过
        if ($user_info['mobile_validated'] == 0)
            $step = 2;
        //原手机验证是否通过
        if ($user_info['mobile_validated'] == 1 && session('mobile_step1') == 1)
            $step = 2;
        if ($user_info['mobile_validated'] == 1 && session('mobile_step1') != 1)
            $step = 1;
        if (IS_POST) {
            $mobile = I('post.mobile');
            $code = I('post.code');
            $info = session('mobile_code');
            if (!$info)
                $this->error('非法操作');
            if ($info['email'] == $mobile || $info['code'] == $code) {
                if ($user_info['email_validated'] == 0 || session('email_step1') == 1) {
                    session('mobile_code', null);
                    session('mobile_step1', null);
                    if (!$userLogic->update_email_mobile($mobile, $this->id, 2))
                        $this->error('手机已存在');
                    $this->success('绑定成功', U('Home/User/index'));
                } else {
                    session('mobile_code', null);
                    session('email_step1', 1);
                    redirect(U('Home/User/mobile_validate', array('step' => 2)));
                }
                exit;
            }
            $this->error('验证码手机不匹配');
        }
        $this->assign('step', $step);
        return $this->fetch();
    }


    /*
     * 密码修改
     */
    public function password()
    {
        //检查是否第三方登录用户
        $logic = new UserService();
        $data = $logic->get_info($this->id);
        $user = $data['result'];
        if ($user['mobile'] == '' && $user['email'] == '')
            $this->error('请先到电脑端绑定手机', U('/Mobile/User/index'));
        if (IS_POST) {
            $userLogic = new UserService();
            $data = $userLogic->password($this->id, I('post.old_password'), I('post.new_password'), I('post.confirm_password')); // 获取用户信息
            if ($data['status'] == -1)
                return $this->ajaxReturn($data);
            return $this->ajaxReturn($data);
            exit;
        }
        return $this->fetch();
    }

    function forget_pwd()
    {
        $username = I('username');
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            return $this->fetch();
        } else if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!empty($username)) {
                $user = M('user')->where('mobile', $username)->find();
                if ($user) {
                    session('forget_mobile', $username);
                    return $this->ajaxReturn(array('username' => $username));
                } else {
                    return $this->ajaxReturn(array('msg' => '用户名不存在，请检查'));

                }
            }
        }


    }
    function find_pwd() {
        $this->assign('mobile', session('forget_mobile'));
        return $this->fetch();
    }


    function check_validate_code()
    {
        $type = I('type', 'mobile');
        $code = I('code', 0);
        $mobile = session('forget_mobile');
        $util = new service\Util();
        $res = $util->check_validate_code($code, $mobile, $type);
        if($res['status']) {
            $util->redis->setex('i_' . $type . '_' . $mobile, 300, 'ok');
            $res['code'] = md5(time() + rand(1000, 9999));
        }
        $this->ajaxReturn($res);
    }

    public function set_pwd()
    {
        $mobile = session('forget_mobile');
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $util = new service\Util();
            $check = $util->redis->get('i_mobile_' . $mobile);

            $password = I('post.password');
            $password2 = I('post.password2');
            if ($password2 != $password) {
                return $this->ajaxReturn(array('status' => 0, 'msg' => '两次密码不一致'));
            }
            if ($check == 'ok') {
                $user = M('user')->where("mobile", $mobile)->find();
                M('user')->where("id", $user['id'])->update(array('password' => encrypt_user($password)));
                $util->redis->delete('i_mobile_' . $mobile);
                session('forget_mobile', null);
                $this->ajaxReturn(array('status' => 1, 'msg' => '新密码已设置行牢记新密码'));
            } else {
                return $this->ajaxReturn(array('status' => 0, 'msg' => '验证码已失效'));
            }
        } else if($_SERVER['REQUEST_METHOD'] == 'GET' && $mobile){
            return $this->fetch();
        }

    }

}