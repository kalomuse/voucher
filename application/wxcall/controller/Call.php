<?php
namespace app\wxcall\controller;

use think\Controller;
use app\base\service\Jssdk;

class Call extends Controller
{
    //消息处理
    public function index()
    {
        #echo $_GET['echostr'];exit();
        $xml = file_get_contents("php://input");
        $msg = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($msg['Event'] == "CLICK") {
            switch ($msg['EventKey']) {
                case "about":
                    $content = "云水居士、佮名赵云萍、东北人。曾就读于哈尔滨师范大学，古汉语专业。出生于书香门庭，自幼熟读古籍，对古典文学，易学易理，中医道法颇有研究。并已成名二十余年。吾辈本着行善积德，益于众生的原则，运行紫薇斗数，与人解厄改命。签订风水数码解密，车牌号、手机号、吉凶鉴定等等。";
                    $res = $this->transmitText($msg, $content);
                    echo $res;
                    break;
            }
        }
    }   

    private function transmitText($msg, $content) {
        $time = time();
        $textTpl = "<xml>
        <ToUserName><![CDATA[{$msg['FromUserName']}]]></ToUserName>
        <FromUserName><![CDATA[{$msg['ToUserName']}]]></FromUserName>
        <CreateTime>$time</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content><![CDATA[$content]]></Content>
        </xml>";
            return $textTpl;
    }
}
