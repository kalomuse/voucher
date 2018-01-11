<?php
namespace app\admin\service;

class TableService
{
    private $table;
    public function __construct($table)
    {
        $this->table = $table;
    }

    public function query($params=array()) {
        $query = M($this->table);
        $count_query =  M($this->table);

        if(count($params)) {
            $query = $query->where($params);
            $count_query = $count_query->where($params);
        }
        $cur = $_REQUEST['page']; //当前页
        $limit = $_REQUEST['rows']; //每页行数
        $order = 'id desc';
        if($_REQUEST['sidx'])
            $order = "{$_REQUEST['sidx']} {$_REQUEST['sord']}"; //排序的列

        $total = ceil( $count_query->count() / $limit);
        $query = $query->order($order)->limit(($cur - 1) * $limit, $limit)->select();


        $query = array(
            'page'=>$cur,
            'total'=> $total,
            'rows'=> $query,
        );
        return $query;
    }

    public function update_cell()
    {
        unset($_POST['oper']);
        $id = $_POST['id'];
        unset($_POST['id']);
        M($this->table)->where("id", $id)->update($_POST);
        return true;
    }

    public function edit($params = array())
    {
        if($_POST['oper'] && $_POST['oper'] == 'del') { //删除行
            $id = $_POST['id'];
            M($this->table)->where("id", $id)->delete();
        } else if($_POST['oper'] && $_POST['oper'] == 'add') { //插入
            unset($_POST['oper']);
            if(isset($_POST['birth']))
                $_POST['old'] = $this->calcAge($_POST['birth']);
            $id = M($this->table)->insert($_POST, 0, 1);

            return array(
                'msg'=> '插入成功',
                'id'=> $id,
            );
        } else if($_POST['oper'] && $_POST['oper'] == 'edit'){//编辑整行
            unset($_POST['oper']);
            M($this->table)->where('id', $_GET['id'])->update($_POST);
            $res = M($this->table)->where('id', $_GET['id'])->find();
            return array(
                'msg'=> '插入成功',
                'id'=> $res['id'],
                'file'=> $res['file'],
            );

        }
        return true;

    }
}
