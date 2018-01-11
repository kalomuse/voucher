<?php


namespace app\mobile\service;
use think\Model;
use think\Page;
use think\db;
use Util;
/**
 * 分类逻辑定义
 * Class CatsLogic
 * @package Home\Logic
 */
class UserService extends Model
{
    /*
     * 编号登录
     */
    public function do_login($username,$password){
        if(!$username || !$password) {
            return array('status' => 0, 'msg' => '请填写手机号或密码');
        }
        $password = md5(C('salt').$password);
        $user = M('user')->where("mobile","=",$username)->find();
        if(!$user){
            $result = array('status'=>-1,'msg'=>'账号不存在!');
        }elseif($password != $user['password']){
            $result = array('status'=>-2,'msg'=>'密码错误!');
        }elseif($user['is_lock'] == 1){
            $result = array('status'=>-3,'msg'=>'账号异常已被锁定！！！');
        }else{
            M('user')->where("id", $user['id'])->update(array('last_login'=>time()));
            $result = array('status'=>1,'msg'=>'登陆成功','result'=>$user);
        }
        return $result;
    }

    //绑定账号
    public function oauth_bind($data = array()){
        $user = session('user');
        if(empty($user['openid'])){
            if(M('user')->where(array('openid'=>$data['openid']))->count()>0){
                return array('status'=>-1,'msg'=>'您的'.$data['oauth'].'账号已经绑定过账号');
            }else{
                M('user')->where(array('id'=>$user['id']))->update($data);
                return array('status'=>1,'msg'=>'绑定成功','result'=>$data);
            }
        }else{
            return array('status'=>-1,'msg'=>'您的账号已绑定过，请不要重复绑定');
        }
    }
    /*
     * 第三方登录
     */
    public function thirdLogin($data=array()){
        $openid = $data['openid']; //第三方返回唯一标识
        $oauth = $data['oauth']; //来源
        if(!$openid || !$oauth)
            return array('status'=>-1,'msg'=>'参数有误','result'=>'');

        $user = get_user_info($openid, 3, $oauth);
        if(!$user){
            //账户不存在 注册一个
            $map['password'] = '';
            $map['openid'] = $openid;
            $map['name'] = $data['nickname'];
            $map['oauth'] = $oauth;
            $map['file'] = $data['head_pic']. "||{$data['nickname']}.png";
            $map['sex'] = $data['sex'] == 2 ? '女' : '男' ;
            $row_id = M('user')->insertGetId($map);
            $user = M('user')->where("id", $row_id)->find();

        }else{
            M('user')->where("id", $user['id'])->update(array('last_login'=>time()));
        }
        return array('status'=>1,'msg'=>'登陆成功','result'=>$user);
    }

    /**
     * 注册
     * @param $username  邮箱或手机
     * @param $password  密码
     * @param $password2 确认密码
     * @return array
     */
    public function reg($username,$password,$password2){
        $is_validated = 0 ;
        if(check_mobile($username)){
            $is_validated = 1;
            $map['mobile'] = $username; //手机注册
        }

        if($is_validated != 1)
            return array('status'=>-1,'msg'=>'请用手机号注册');

        if(!$username || !$password)
            return array('status'=>-1,'msg'=>'请输入用户名或密码');

        //验证两次密码是否匹配
        if($password2 != $password)
            return array('status'=>-1,'msg'=>'两次输入密码不一致');
        //验证是否存在用户名
        if(get_user_info($username,1)||get_user_info($username,2))
            return array('status'=>-1,'msg'=>'账号已存在');

        $map['leader'] = I('code', '');
        $map['password'] = encrypt_user($password);
        $map['reg_time'] = time();

        $id = M('user')->insertGetId($map);
        if($id === false)
            return array('status'=>-1,'msg'=>'注册失败');

        $user = M('user')->where("id", $id)->find();
        return array('status'=>1,'msg'=>'注册成功','result'=>$user);
    }

    /*
     * 获取当前登录用户信息
     */
    public function get_info($id){
        if(!$id > 0)
            return array('status'=>-1,'msg'=>'缺少参数','result'=>'');
        $user_info = M('user')->where("id", $id)->find();
        if(!$user_info)
            return false;
     return array('status'=>1,'msg'=>'获取成功','result'=>$user_info);
    }

    /**
     * 邮箱或手机绑定
     * @param $email_mobile  邮箱或者手机
     * @param int $type  1 为更新邮箱模式  2 手机
     * @param int $user_id  用户id
     * @return bool
     */
    public function update_email_mobile($email_mobile,$id,$type=1){
        //检查是否存在邮件
        if($type == 1)
            $field = 'email';
        if($type == 2)
            $field = 'mobile';
        $condition['id'] = array('neq',$id);
        $condition[$field] = $email_mobile;

        $is_exist = M('user')->where($condition)->find();
        if($is_exist)
            return false;
        unset($condition[$field]);
        $condition['id'] = $id;
        $validate = $field.'_validated';
        M('user')->where($condition)->update(array($field=>$email_mobile,$validate=>1));
        return true;
    }

    /**
     * 更新用户信息
     * @param $user_id
     * @param $post  要更新的信息
     * @return bool
     */
    public function update_info($id,$post=array()){
        $model = M('user')->where("id", $id);
        $row = $model->setField($post);
        if($row === false)
            return false;
        return true;
    }


    /**
     * 修改密码
     * @param $user_id  用户id
     * @param $old_password  旧密码
     * @param $new_password  新密码
     * @param $confirm_password 确认新 密码
     */
    public function password($id,$old_password,$new_password,$confirm_password,$is_update=true){
        $data = $this->get_info($id);
        $user = $data['result'];
        if(strlen($new_password) < 6)
            return array('status'=>-1,'msg'=>'密码不能低于6位字符','result'=>'');
        if($new_password != $confirm_password)
            return array('status'=>-1,'msg'=>'两次密码输入不一致','result'=>'');
        //验证原密码
        if($is_update && ($user['password'] != '' && encrypt_user($old_password) != $user['password']))
            return array('status'=>-1,'msg'=>'密码验证失败','result'=>'');
        $row = M('user')->where("id", $id)->update(array('password'=>encrypt_user($new_password)));
        if(!$row)
            return array('status'=>-1,'msg'=>'修改失败','result'=>'');
        return array('status'=>1,'msg'=>'修改成功','result'=>'');
    }


}