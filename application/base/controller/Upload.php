<?php
namespace app\base\controller;
use think\Controller;
use think\Session;

class Upload extends Controller
{

    public function img()
    {
        Session::start();
        $file = request()->file('file');
        if($file) {
            $base_path = '/public/img/upload/';
            $info = $file->move(ROOT_PATH . $base_path);
            $file_name = $file->getInfo()['name'] ;
            $file_path = $base_path . $info->getSaveName();// . '||' . $file_name;
            if(isset($_GET['id']) && isset($_GET['type'])) {
                $file_path .= '||' . $file_name;
                if($_GET['type'] == 'rand_voucher' || $_GET['type'] == 'buy_voucher') {
                    $query = M($_GET['type'])->where('id', $_GET['id'])->find();

                    if($query['file']) {
                        $img = explode(',', $query['file']);
                    } else {
                        $img = array();
                    }
                    $img[] = $file_path;
                    $set = array(
                        'file' => implode(',', $img),
                    );
                    M($_GET['type'])->where('id', $_GET['id'])->update($set);
                }
            } else if(isset($_GET['is_create']) && isset($_GET['type'])) { //如果不存在id，则创建数据
                if(!isset($_GET['id'])) {
                    $file_path .= '||' . $file_name;
                    $set = array(
                        'file' => $file_path,
                    );
                    $id = M($_GET['type'])->insert($set, '', 1);
                    return exit(json_encode(array('is_create'=>1, 'id'=>$id)));
                }
            }
        }
    }
}
