<?php
namespace app\admin\controller;
use app\admin\validate\LoginValidate;
use think\Controller;
use think\Session;

class Login extends Controller
{
    /**
     * 用户登录
     */
    public function index()
    {
        Session::start();
        $method = $_SERVER['REQUEST_METHOD'];
        // 判断提交方式
        if ($method == 'POST') {
            $validate = new LoginValidate();

            // 实例化Login对象
            if(!$validate->check($_POST)){
                $err = $validate->getError();
                return $this->ajaxReturn($err);
            }

            $result = M('admin')->where("username=\"{$_POST['username']}\" and password=\"{$_POST['password']}\"")->find();
            // 验证用户名 对比 密码
            if ($result) {
                // 存储session
                $_SESSION['admin']['id'] = $result['id'];          // 当前用户id
                $_SESSION['admin']['nickname'] = $result['nickname'];   // 当前用户昵称
                $_SESSION['admin']['username'] = $result['username'];   // 当前用户名
                $_SESSION['admin']['role'] = $result['role'];
                return $this->ajaxReturn(array('status'=>'ok'));
            } else {
                return $this->ajaxReturn('登录失败,用户名或密码不正确!');
            }
        } else {
            return $this->fetch();
        }
    }
    public function logout()
    {
        // 清楚所有session
        session(null);
        header("location: /admin/login");
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
}