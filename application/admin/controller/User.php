<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/7/27
 * Time: 下午1:32
 */
namespace app\admin\controller;
use app\admin\service\TableService;

class User extends Base
{
    private $tableService;
    public function _initialize()
    {
        parent::_initialize();
        $this->tableService = new TableService('user');
    }

    public function index()
    {
        return $this->fetch();
    }
    public function distribut()
    {
        return $this->fetch();
    }
    public function ajax_fetch()
    {
        $set = array();
        if(I('role'))
            $set['role'] = 'distribut';
        $data = $this->tableService->query($set);
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
    public function delete_file() {
        $contract = M('user')->where('id', $_POST['id'])->find();
        $file = explode(',', $contract['file']);
        unset($file[$_POST['index']]);
        if($file) {
            $file = implode(',', $file);
        } else {
            $file = '';
        }
        M('user')->where('id', $_POST['id'])->update(array('file'=>$file));
        $this->ajaxReturn('修改成功');
    }

}