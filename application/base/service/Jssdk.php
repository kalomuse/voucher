<?php

namespace app\base\service;
use think\Model;
use think\Db;
require_once PROJECT_PATH."/application/function.php";
/**
 * 分类逻辑定义
 * Class CatsLogic
 * @package Home\Logic
 */
class Jssdk extends Model
{

    private $appId;
    private $appSecret;

    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }
    // 签名
    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "rawString" => $string,
            "signature" => $signature

        );
        return $signPackage;
    }
// 随机字符串
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    /**
     * 根据 access_token 获取 icket
     * @return type
     */
    public function getJsApiTicket(){
        $ticket = S('ticket');
        if(!empty($ticket))
            return $ticket;

        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
        $return = httpRequest($url,'GET');
        $return = json_decode($return,1);
        if(isset($return['ticket']))
            S('ticket',$return['ticket'],7000);
        return $return['ticket'];
    }


    /**
     * 获取 网页授权登录access token
     * @return type
     */
    public function getAccessToken(){
        //判断是否过了缓存期
        $access_token = S('access_token');
        if(!empty($access_token))
            return $access_token;

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
        $return = httpRequest($url,'GET');
        $return = json_decode($return,1);
        S('access_token',$return['access_token'],7000);
        return $return['access_token'];
    }

    /**
     * 发送一次性订阅消息
     * @return type
     */
    public function sendOnceTemplate($openid, $shop='', $item='', $orderid=''){
        //判断是否过了缓存期
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/subscribe?access_token=$access_token";
        $data = array(
            "touser" => $openid,
            "template_id" => "-wxyfDRWb0n-o-LjVIxeSl-VuXC8y82kOQyZTq6dxbc",
            "url"=>SITE_URL. '/mobile/index/mine?id=1',
            "scene"=> "SCENE",
            "title"=> "恭喜你成功获得$item",
            "data"=> array(
                "content"=> array(
                    "value"=>"请点击查收",
                    "color"=>"#00B2EE",
                ),
            )
        );
        $return = httpRequest($url,'POST',json_encode($data));

    }


    /**
     * 发送消息模板
     * @return type
     */
    public function sendTemplate($openid, $shop='', $item='', $orderid=''){
        //判断是否过了缓存期
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
        $data = array(
            "touser" => $openid,
            "template_id" => "LFeyG0X3vl94UwsTuUER4dLk4Qy9Pi9kDnrl0keAQgE",
            "url"=>SITE_URL. '/mobile/index/mine?id=1',
            "data"=> array(
                "first"=> array(
                    "value"=>"恭喜你成功获得$item",
                ),
                "keyword1"=> array(
                   "value"=>'（兑换码）'.$orderid,
                ),
                "keyword2"=> array(
                    "value"=>"中奖提示",
                ),
                "keyword3"=> array(
                    "value"=> "已中奖",
                ),
                "keyword4"=> array(
                    "value"=> $shop,
                ),

               "remark"=> array(
                    "value"=>"点击跳转，领票时请向工作人员出示兑换码！",
                   "color"=>"#ff0000"
                )
            )
        );
        $return = httpRequest($url,'POST',json_encode($data));

    }

    // 获取一般的 access_token
    public function get_access_token($name=''){
        if($name=='') {
            $conf = include(APP_PATH.'base/conf/conf.php');
            $name = $conf['wx']['alias'];
        }

        $util = new Util();
        $token = $util->redis->get('access_token_'.$name);
        if($token){
            return $token;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$util->conf['wx']['appid']}&secret={$util->conf['wx']['appsecret']}";
        $return = httpRequest($url,'GET');
        $return = json_decode($return,1);
        $util->redis->setex('access_token_kaka', 7000, $return['access_token']);
        return $return['access_token'];
    }

    /*
     * 向用户推送消息
     */
    public function push_msg($openid,$content){
        $access_token = $this->get_access_token();
        $url ="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $post_arr = array(
            'touser'=>$openid,
            'msgtype'=>'text',
            'text'=>array(
                'content'=>$content,
            )
        );
        $post_str = json_encode($post_arr,JSON_UNESCAPED_UNICODE);
        $return = httpRequest($url,'POST',$post_str);
        $return = json_decode($return,true);
    }



    // 网页授权登录获取 OpendId
    public function GetOpenid($type='')
    {
        if ($type == '' && isset($_SESSION['openid']))
            return $_SESSION['openid'];
        //通过code获得openid
        if (!isset($_GET['code'])) {
            $this->redirect_url();
        } else {
            //上面获取到code后这里跳转回来
            $code = $_GET['code'];
            $data = $this->getOpenidFromMp($code);//获取网页授权access_token和用户openid
            if($type == 'pay')
                return $data['openid'];
            $data2 = $this->GetUserInfo($data['access_token'], $data['openid']);//获取微信用户信息
            $data['nickname'] = empty($data2['nickname']) ? '微信用户' : trim($data2['nickname']);
            $data['sex'] = $data2['sex'];
            $data['head_pic'] = $data2['headimgurl'];
            //$data['subscribe'] = $data2['subscribe'];
            $_SESSION['openid'] = $data['openid'];
            $data['oauth'] = 'weixin';
            if (isset($data2['unionid'])) {
                $data['unionid'] = $data2['unionid'];
            }
            return $data;
        }
    }

    /**
     * 获取当前的url 地址
     * @return type
     */
    private function redirect_url()
    {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
        $url = $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
        $baseUrl = urlencode('http://oauth.tikyy.com/wx/redirect?redirect=' . $url);
        $url = $this->__CreateOauthUrlForCode($baseUrl);
        Header("Location: $url"); // 跳转到微信授权页面 需要用户确认登录的页面
        exit();
    }


    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        $res = httpRequest($url, 'GET');
        $data = json_decode($res, true);
        return $data;
    }

    /**
     *
     * 通过access_token openid 从工作平台获取UserInfo
     * @return openid
     */
    public function GetUserInfo($access_token, $openid)
    {
        // 获取用户 信息
        $url = $this->__CreateOauthUrlForUserinfo($access_token, $openid);
        $res = httpRequest($url, 'GET');
        $data = json_decode($res, true);
        //获取用户是否关注了微信公众号， 再来判断是否提示用户 关注
        if (!isset($data['unionid'])) {
            $access_token2 = $this->get_access_token();//获取基础支持的access_token
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token2&openid=$openid";
            $subscribe_info = httpRequest($url, 'GET');
            $subscribe_info = json_decode($subscribe_info, true);
            //$data['subscribe'] = $subscribe_info['subscribe'];
        }
        return $data;
    }








    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->appId;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
//        $urlObj["scope"] = "snsapi_base";
        $urlObj["scope"] = "snsapi_userinfo";
        $urlObj["state"] = "STATE" . "#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code ，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->appId;
        $urlObj["secret"] = $this->appSecret;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
    }

    /**
     *
     * 构造获取拉取用户信息(需scope为 snsapi_userinfo)的url地址
     * @return 请求的url
     */
    private function __CreateOauthUrlForUserinfo($access_token, $openid)
    {
        $urlObj["access_token"] = $access_token;
        $urlObj["openid"] = $openid;
        $urlObj["lang"] = 'zh_CN';
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/userinfo?" . $bizString;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

}