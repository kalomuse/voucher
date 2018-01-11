<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/7/27
 * Time: 下午1:32
 */
namespace app\admin\controller;
use app\admin\service\TableService;

class Role extends Base
{
    private $tableService;
    public function _initialize()
    {
        parent::_initialize();
        $this->tableService = new TableService('admin');
    }
    public function list ()
    {
        $role = M('role')->select();

        $str = '';
        foreach ($role as $d) {
            $map[$d['id']] = $d['name'];
            $str .= "{$d['id']}:\"{$d['name']}\",";
        }
        $select = "{value:{{$str}}}";
        $map['admin'] = "超级管理员";
        $map = json_encode($map);
        $this->assign('map', $map);
        $this->assign('select', $select);
        return $this->fetch();
    }

    public function delete_role()
    {
        $id = $_POST['id'];
        M('role')->delete(array('id' => $id));
        return $this->ajaxReturn(array('ok' => 1, 'msg' => '删除成功'));
    }

    public function add_role()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            return $this->fetch();
        } else {
            $set = array(
                'name' => $_POST['name'],
                'role' => json_encode($_POST['role']),
            );
            M('role')->insert($set);
            return $this->ajaxReturn(array('ok' => 1, 'msg' => '创建成功'));
        }

    }

    public function edit_role()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == 'GET') {
            $data = M('role')->where('id', $_GET['id'])->find();
            $data['role'] = json_decode($data['role'],1);
            $this->assign('id', $_GET['id']);
            $this->assign('data', $data);
            return $this->fetch();
        } else {
            $set = array(
                'name' => $_POST['name'],
                'role' => json_encode($_POST['role']),
            );
            M('role')->where('id', $_POST['id'])->update($set);
            return $this->ajaxReturn(array('ok'=>1, 'msg'=>'修改成功'));
        }

    }

    public function to()
    {
        $data = M('role')->select();
        $this->assign('data', $data);
        return $this->fetch();
    }
    public function ajax_fetch() {
        $data = $this->tableService->query();
        return $this->ajaxReturn($data);
    }

    public function ajax_cell()
    {
        $this->tableService->update_cell();
        return true;

    }

    public function ajax_edit()
    {
        $data = $this->tableService->edit();
        return $this->ajaxReturn($data);

    }
}
