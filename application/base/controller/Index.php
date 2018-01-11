<?php
namespace app\base\controller;
use think\Controller;
use app\mobile\service\OrderService;

class Index extends Controller
{
    public function index ()
    {
        $order_service  = new OrderService();
        $order = array(
            'transaction_id' => 'adsfasdfasdf',
            'out_trade_no' => 'i15154965955571'
        );
        $order_service->pay_order($order);
    }
    public function wx() {
        $conf = include(APP_PATH.'base/conf/conf.php');
        $appid = $conf['wx']['appid'];
        $template = '-wxyfDRWb0n-o-LjVIxeSl-VuXC8y82kOQyZTq6dxbc';
        $redirect_url = urlencode(SITE_URL.'/');
        $url = "https://mp.weixin.qq.com/mp/subscribemsg?action=get_confirm&appid={$appid}&scene=1000&template_id=$template&redirect_url={$redirect_url}&reserved=test#wechat_redirect";

        $this->assign('url', $url);
        return $this->fetch();
    }

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
}
