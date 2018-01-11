<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/7/27
 * Time: 下午1:32
 */
namespace app\admin\controller;
use app\admin\service\TableService;

class Buyvoucher extends Base
{
    private $tableService;
    public function _initialize()
    {
        parent::_initialize();
        $this->table = I('table', 'buy_voucher');
        $this->tableService = new TableService($this->table);
    }

    public function buy()
    {
        return $this->fetch();
    }
    public function buylist()
    {
        return $this->fetch();
    }

    public function ajax_fetch()
    {
        $params = array();
        if($this->table == 'join') {
            $params = array(
                'aid'=> I('id'),
                'status'=> 2,
            );
        }
        $data = $this->tableService->query($params);
        return $this->ajaxReturn($data);
    }

    public function ajax_cell()
    {

        $this->tableService->update_cell();
        return true;
    }

    public function ajax_edit()
    {
        if(isset($_POST['price'])) {
            if ($_POST['price'] == 0)
                $_POST['remark'] = 1;
            else $_POST['remark'] = 2;
        }
        $data = $this->tableService->edit();
        return $this->ajaxReturn($data);

    }
    public function delete_file() {
        $contract = M($this->table)->where('id', $_POST['id'])->find();
        $file = explode(',', $contract['file']);
        unset($file[$_POST['index']]);
        if($file) {
            $file = implode(',', $file);
        } else {
            $file = '';
        }
        M($this->table)->where('id', $_POST['id'])->update(array('file'=>$file));
        $this->ajaxReturn('修改成功');
    }

}