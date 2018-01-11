<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/7/27
 * Time: 下午1:32
 */
namespace app\admin\controller;
use app\admin\service\TableService;

class Bill extends Base
{
    private $tableService;
    public function _initialize()
    {
        parent::_initialize();
        $this->table = I('type', 'bill');
        $this->tableService = new TableService($this->table);
    }
    public function list() {
        return $this->fetch();
    }

    public function order() {
        return $this->fetch();
    }

    public function ajax_fetch()
    {
        $data = $this->tableService->query();
        return $this->ajaxReturn($data);
    }

    public function ajax_cell()
    {
        exit();

    }

    public function ajax_edit()
    {
        exit();

    }

}