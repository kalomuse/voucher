<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/7/27
 * Time: 下午1:32
 */

namespace app\admin\controller;
use think\Controller;
use think\Session;


class Base extends Controller
{
    public $access;
    public function _initialize()
    {
        Session::start();
        header("Cache-control: private");  // history.back返回后输入框值丢失问题 参考文章 http://www.tp-shop.cn/article_id_1465.html  http://blog.csdn.net/qinchaoguang123456/article/details/29852881
        $this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID', $this->session_id); //将当前的session_id保存为常量，供其它方法调用

        //登录重定向
        if(!isset($_SESSION['admin']) && $_SERVER['REQUEST_URI'] != '/admin/login') {
            header("location: /admin/login");
            exit();
        }

        //获取指向module
        $path_array = explode('/', $_SERVER['REQUEST_URI']);
        if(count($path_array) >= 4) {
            $this->assign('module', $path_array[2]);
            $module = $path_array[2];
            $action = $path_array[3];
        } else if(count($path_array) == 3) {
            $module = $path_array[2];
            $action = C('default_action');
        }  else if(count($path_array) == 2) {
            $module = C('default_controller');
            $action = C('default_action');
        }

        //权限控制
        $menu = include(APP_PATH."admin/conf/menu.php");
        $access_menu = $menu;
        if($_SESSION['admin']['role'] != 'admin') {
            $role = M('role')->where('id', $_SESSION['admin']['role'])->find();
            $role = json_decode($role['role'], 1);

            foreach ($access_menu as $mod_index => &$mod_item) {
                $mod = $mod_item['name'];
                if(isset($role[$mod])) {
                    foreach ($mod_item['child'] as $act_index => &$act_item) {
                        if(isset($role[$mod][$act_index]) && intval($role[$mod][$act_index] == 1)) {

                        } else {
                            unset($mod_item['child'][$act_index]);
                        }
                    }
                    if (!$mod_item['child']) {
                        unset($access_menu[$mod_index]);
                    }
                } else {
                    unset($access_menu[$mod_index]);
                }
            }

            $first_mod = current($access_menu);
            $first_action = current($first_mod['child'])['action'];
            foreach ($menu as $mod_index => $mod_item) {
                if($mod_item['name'] == $module) {
                    foreach ($mod_item['child'] as $action_index => $action_item) {
                        if ($action_item['action'] == $action) {
                            if (!intval($role[$module][$action_index])) {
                                Header("Location: /admin/".$first_mod['name'].'/'.$first_action);
                            }
                        }
                    }
                }
            }

        }

        $this->assign('access_menu', $access_menu);
        $this->assign('menu', $menu);
        $this->assign('module', $module);
        $this->assign('action', $action);
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