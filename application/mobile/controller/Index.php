<?php

namespace app\mobile\controller;
use think\DB;

class Index extends Base
{
    public function vlist() {
        $id = base64_decode(I('code'));
        $pwd = I('pwd', '');
        $type = I('type', 'chou');
        if(IS_GET)
            return $this->fetch();
        if($id && $pwd) {
            $query = array(
                'aid'=> $id,
                'is_use'=> 0,
                'pwd'=> strtoupper($pwd)
            );
            if($type == 'chou')
                $table = 'join';

            else if($type == 'buy')
                $table = 'buy_join';

            $has = M($table)->where($query)->find();
            if(!$has)
                return $this->ajaxReturn('兑换码不存在，请检查是否有误');
            $set = array(
                'is_use'=> 1
            );
            M($table)->where('pwd', $pwd)->update($set);
            return $this->ajaxReturn(array('status'=>'ok'));

        }
        return $this->ajaxReturn('兑换码不能为空');
    }
    public function Index() {
        if(IS_GET) {
            $this->assign('tab', 'index');
            return $this->fetch();
        }

        $data = array(
            "content" => array(
                array(
                    'title' => "猜你喜欢",
                    'list' => array(),
                ),
            ),
        );

        $sql = 'select * from (select * from buy_voucher union all select * from rand_voucher) as c order by created_time desc';
        $voucher = Db::query($sql);
        foreach ($voucher as &$v) {
            $tmp = explode(',', $v['file']);
            $files = array();
            foreach ($tmp as $f) {
                if($f) $files[] = explode('||', $f)[0];
            }
            $v['file'] = count($files)? $files[0]: '';
            $tmp = array(
                "title" => $v['title'],
                "imgSrc" => $v['file'],
                "text"=> "专场推荐",
                "url"=> "/mobile/index/detail?id={$v['id']}&type=".$v['remark'],
                "remark"=> C('type')[intval($v['remark'])],
                "type" => $v['remark'],
            );
            $data['content'][0]['list'][] = $tmp;
        }
        return $this->ajaxReturn($data);

    }

    public function history() {
        $type = I('type');
        if(IS_GET) {
            $this->assign('tab', 'history');
            return $this->fetch();
        }

        $data = array(
            "content" => array(
                array(
                    'title' => "猜你喜欢",
                    'list' => array(),
                ),
            ),
        );

        $query = array(
            'is_end' => 1,
        );
        if($type == 'choujiang')
            $voucher = M('rand_voucher')->where($query)->select();
        else if($type == 'buy')
            $voucher = M('buy_voucher')->where($query)->select();

        foreach ($voucher as &$v) {
            $tmp = explode(',', $v['file']);
            $files = array();
            foreach ($tmp as $f) {
                if($f) $files[] = explode('||', $f)[0];
            }
            $v['file'] = count($files)? $files[0]: '';
            $tmp = array(
                "title" => $v['title'],
                "imgSrc" => $v['file'],
                "date"=> substr($v['end_time'],0,10),
                "text"=> $v['title'],
                "url"=> "",
                "remark"=> C('type')[intval($v['remark'])],
                "type" => $v['remark'],
            );
            $data['content'][0]['list'][] = $tmp;
        }
        return $this->ajaxReturn($data);
    }

    public function message() {
        if(IS_GET) {
            $this->assign('tab', 'message');
            return $this->fetch();
        }
    }
    public function can_buy() {
        $id = I('id');
        $voucher = M('buy_voucher')->where('id', $id)->find();
        $count = M('buy_join')->where('aid', $voucher['id'])->count();
        if($voucher['total'] <= $count) {
            return $this->ajaxReturn('优惠券已发放完毕，谢谢参与');
        } else if(strtotime($voucher['end_time']) <= time()) {
            if($voucher['is_end'] == 0)
                M('buy_voucher')->where('id', $id)->update(array('is_end'=>1));
            return $this->ajaxReturn('本次活动已结束，谢谢参与');
        }
        return $this->ajaxReturn(array('status'=>'ok'));
    }
    public function detail() {
        $id = I('id');
        $type = I('type', 0);
        if($type == 1 || $type == 2) {
            $voucher = M('buy_voucher')->where('id', $id)->find();
        } else if($type == 0) {
            $voucher = M('rand_voucher')->where('id', $id)->find();
        }
        if(IS_GET) {
            $tmp = explode(',', $voucher['file']);
            $files = array();
            foreach ($tmp as $f) {
                if(!empty($f)) $files[] = explode('||', $f)[0];
            }

            $this->signPackage['img'] = SITE_URL.$files[0];
            $this->signPackage['link'] = SITE_URL.'/mobile/index/detail?id='.$id. '&type='.$type;
            $this->signPackage['desc'] = $voucher['title'];
            $this->signPackage['title'] =  $voucher['title'];
            $this->assign('signPackage', $this->signPackage);
            $this->assign('tab', 'index');
            return $this->fetch();
        }

        $tmp = explode(',', $voucher['file']);
        $files = array();
        foreach ($tmp as $f) {
            if($f) $files[] = explode('||', $f)[0];
        }
        $voucher['rule'] = '<p>' . implode('</p><p>', explode("\n",$voucher['rule']))  . '</p>';
        if(time() < strtotime($voucher['end_time']))
            $intval = time() - strtotime($voucher['created_time']);
        else
            $intval = strtotime($voucher['end_time']) - strtotime($voucher['created_time']);

        if($type == 1 || $type == 2) {
            $count = M('buy_join')->where('aid', $id)->count();
            $data = array(
                "content" => array(
                    array(
                        "type" => $type,
                        'list' => array(
                            array(
                                "src1"=>isset($files[1]) ? $files[1] : '',
                                "src"=>isset($files[0]) ? $files[0] : '',
                                "text"=>$voucher['title'],
                                "price"=>$voucher['price'],
                                "oldprice"=>$voucher['old_price'],
                                "num"=>$voucher['total'],
                                "xsnum"=> $count,
                                "buydate"=> substr($voucher['end_time'], 0, 10),
                                "is_end" => $voucher['is_end'],
                                "bz"=>$voucher['rule']
                            ),
                        ),
                    ),
                ),
            );
        } else if($type == 0) {
            $sum = M('join')->where('aid', $id)->count();
            $data = array(
                "content" => array(
                    array(
                        "type" => $type,
                        'list' => array(
                            array(
                                "src1" => isset($files[1]) ? $files[1] : '',
                                "src2" => isset($files[2]) ? $files[2] : '',
                                "src" => isset($files[0]) ? $files[0] : '',
                                "type" => $voucher['remark'],
                                "is_end" => $voucher['is_end'],
                                "rule" => $voucher['rule'],
                                "rsnum" => $sum + intval($intval / 300)
                            ),
                        ),
                    ),
                ),
            );
        }
        return $this->ajaxReturn($data);
    }

    public function join() {
        $id = I('id');
        $query = array(
            'aid' => $id,
            'uid' => $_SESSION['user']['id'],
        );
        $res = M('join')->where($query)->find();
        if($res)
            return $this->ajaxReturn(array('status'=>'err', 'msg'=>'请耐心等待开奖'));

        $set = array(
            'aid' => $id,
            'uid' => $_SESSION['user']['id'],
            'name' => I('name'),
            'mobile' => I('mobile'),
        );
        M('join')->insert($set);
        return $this->ajaxReturn(array('status'=>'ok', 'msg'=>'参与成功，请耐心等待开奖'));

    }

    public function mine() {
        if(IS_GET) {
            $this->assign('tab', 'mine');
            return $this->fetch();
        }
        $type = I('type', '');

        $data = array(
            "content" => array(
                array(
                    'title' => "猜你喜欢",
                    'list' => array(),
                ),
            ),
        );
        if($type == 'choujiang') {
            $join = M('join')->where('uid', $_SESSION['user']['id'])->select();
            foreach ($join as $j) {
                $voucher = M('rand_voucher')->where('id', $j['aid'])->find();
                $tmp = explode(',', $voucher['file']);
                $files = array();
                foreach ($tmp as $f) {
                    if ($f) $files[] = explode('||', $f)[0];
                }
                $voucher['file'] = count($files) ? $files[0] : '';
                if ($j['status'] == 1) {
                    $status_code = 1;
                    $status = '等待开奖';
                }else if($j['status'] == 2 && $j['is_use'] == 0) {
                    $status_code = 2;
                    $status = '已中奖, 未使用';
                }else if($j['status'] == 2 && $j['is_use'] == 1) {
                    $status_code = 4;
                    $status = '已消费';
                }else if($j['status'] == 3) {
                    $status_code = 3;
                    $status = '未中奖';
                }
                $data['content'][0]['list'][] = array(
                    "title" => $voucher['title'],
                    "imgSrc" =>  $voucher['file'],
                    "text" => $voucher['title'],
                    "url" => "#",
                    "state_code" => $status_code,
                    "state" => $status,
                    "usestate" => $j['pwd'],
                    "date" => substr($voucher['end_time'], 0, 10)
                );
            }
        }  else if($type == 'buy' || $type == 'get') {
            $join = M('buy_join')->where('uid', $_SESSION['user']['id'])->select();
            foreach ($join as $j) {
                if($type == 'buy') {
                    $query = array(
                        'id'=> $j['aid'],
                        'price' => array('gt', 0)
                    );
                    $voucher = M('buy_voucher')->where($query)->find();
                } else if($type == 'get'){
                    $query = array(
                        'id'=> $j['aid'],
                        'price' => array('eq', 0)
                    );
                    $voucher = M('buy_voucher')->where($query)->find();
                }
                if(!$voucher) continue;
                $tmp = explode(',', $voucher['file']);
                $files = array();
                foreach ($tmp as $f) {
                    if ($f) $files[] = explode('||', $f)[0];
                }
                $voucher['file'] = count($files) ? $files[0] : '';
                if($j['is_use'] == 1) {
                    $status_code = 4;
                    $status = '已消费';
                }else if($j['is_use'] == 0) {
                    $status_code = 2;
                    $status = '未消费';
                }
                $data['content'][0]['list'][] = array(
                    "title" => $voucher['title'],
                    "imgSrc" =>  $voucher['file'],
                    "text" => $voucher['title'],
                    "url" => "#",
                    "state_code" => $status_code,
                    "state" => $status,
                    "usestate" => $j['pwd'],
                    "date" => substr($voucher['end_time'], 0, 10)
                );
            }
        }

        return $this->ajaxReturn($data);

    }

    public function tousu() {
        return $this->fetch();
    }

    public function shop_join() {
        if(IS_GET)
            return $this->fetch();
        $mobile = I('mobile', '');
        $name = I('name', '');
        if(!$name)
            return $this->ajaxReturn('请填写店铺名称');
        if(!$mobile)
            return $this->ajaxReturn('请填写联系方式');
        $set = array(
            'mobile' => $mobile,
            'name' => $name,
        );
        M('shop_join') -> insert($set);
        $this->ajaxReturn('您的申请已提交，我们将尽快核实');
    }
}