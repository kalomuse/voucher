<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/7/27
 * Time: 下午1:32
 */
namespace app\admin\controller;
use app\admin\service\TableService;

class Shop extends Base
{
    private $tableService;
    public function _initialize()
    {
        parent::_initialize();
        $this->tableService = new TableService('shop_join');
    }

    public function index() {
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
