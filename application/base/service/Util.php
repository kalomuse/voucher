<?php
namespace app\base\service;
class Util
{
    public $redis;

    public function __construct()
    {
        $conf = include(APP_PATH.'base/conf/conf.php');
        $this->conf = $conf;
        $conf = $conf['redis'];
        $redis = new \Redis();
        $redis->connect($conf['ip'], $conf['port']);
        $this->redis = $redis;
    }
    public function index()
    {

    }
    public function get_user($id) {
        $user = M('user')->where('id', $id)->find();
        return $user;
    }
    public function check_user() {
        $token = I('token', '');
        $user_id = $this->redis->get('share_'.$token);
        return $user_id;
    }

    /**
     * 修改配置的值
     * @param $type [ insert, update, detete ]
     * @param $fileName [ 配置文件名 ]
     * @param $arrData  [ 保存的数值 数组或者数值 ]
     * @param $index [ 要修改的key值，已||分割，列如：wx_website||tikyy ]
     * @param $newkey [ insert: 插入的新key值, update:修改的key值 ]
     */
    function updateConfig($type, $fileName, $arrData, $index=0, $newkey=false)
    {

        $fileName or die('没有文件名');
        $savePath = CONF_PATH . $fileName . '.php';
        if (file_exists($savePath)) {
            $return = require $savePath;
            $return = (array)$return;
            $index = $keys = explode('||', $index);
        }
        if($type == 'insert') {
            //数据为空的时候,则读取文件信息
            if (empty($arrData)) {
                return false;
            }
            $value = &$return;
            for ($i=0; $i<count($index); $i++) {
                $key = array_shift($keys);
                $value = &$value[$key];
            }
            $value[$newkey] = $arrData;
        } else if($type == 'delete') {
            $value = &$return;
            for ($i=0; $i<count($index) - 1; $i++) {
                $key = array_shift($keys);
                $value = &$value[$key];
            }
            $key = array_shift($keys);
            unset($value[$key]);
        } else if($type == 'update') {
            $value = &$return;
            for ($i=0; $i<count($index); $i++) {
                $key = array_shift($keys);
                if($newkey != false && $i == count($index) - 1) {
                    $temp = $value[$key];
                    unset($value[$key]);
                    $value[$newkey] = $temp;
                    $value = &$value[$newkey];
                    break;
                }
                $value = &$value[$key];
            }
            if(is_array($arrData)) { //如果是数组，依次替换里面的键值
                foreach ($arrData as $k => $item) {
                    $value[$k] = $item;
                }
            }else { //如果是值，直接替换
                $value = $arrData;
            }
        }

        $str = var_export($return, true);
        $str = '<?php return ' . $str . ';';
        file_put_contents($savePath, $str);
    }


    public function check_validate_code($code, $mobile, $type ='mobile'){
        $rcode = $this->redis->get('i_'.$type.'_'.$mobile);
        if($rcode == $code)
            return array('status'=>1);
        else
            return array('status'=>-1,'msg'=>'验证码不正确');
        return $res;
    }

    /**
     * 前端发送短信方法: APP/WAP/PC 共用发送方法
     */
    public function send_validate_code() {
        $mobile = $_REQUEST['mobile'];
        $type = $_REQUEST['type'];
        $redis = $this->redis;

        //随机一个验证码
        $code = rand(1000, 9999);
        $params['code'] = $code;
        $redis->setex('i_'.$type.'_'.$mobile, 300, $code);

        //发送短信
        if($type == 'mobile') {

            $resp = $this->send_message($mobile, $params);
        }
        else {
            $msg = '您的验证码为:' . $code . '【洗呗】';
            if ($this->send_email($mobile, '洗呗验证码', $msg)) {
                $resp = array('status' => 1, 'msg' => '发送成功');
            } else
                $resp = array('status' => 0, 'msg' => '发送失败');
        }
        if($resp['status'] == 1){
            $return_arr = array('status'=>1,'msg'=>'发送成功,请注意查收');
        }else{
            $return_arr = array('status'=>-1,'msg'=>'发送失败'.$resp['Message']);
        }
        return $return_arr;

    }

    function send_message($mobile, $smsParams) {
        $smsParams = json_encode($smsParams);
        $conf = $this->conf['message'];
        include PLUGIN_PATH.'message/Config.php';
        include_once PLUGIN_PATH.'message/SendSmsRequest.php';
        include_once PLUGIN_PATH.'message/QuerySendDetailsRequest.php';

        //此处需要替换成自己的AK信息
        $accessKeyId = $conf['appKey'];
        $accessKeySecret = $conf['secretKey'];
        $templateCode = $conf['templateCode'];
        $smsSign = $conf['smsSign'];
        //短信API产品名
        $product = "Dysmsapi";
        //短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region
        $region = "cn-hangzhou";

        //初始化访问的acsCleint
        $profile = \DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        \DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        $acsClient= new \DefaultAcsClient($profile);

        $request = new \Dysmsapi\Request\V20170525\SendSmsRequest;
        //必填-短信接收号码
        $request->setPhoneNumbers($mobile);
        //必填-短信签名
        $request->setSignName($smsSign);
        //必填-短信模板Code
        $request->setTemplateCode($templateCode);

        //选填-假如模板中存在变量需要替换则为必填(JSON格式)
        $request->setTemplateParam($smsParams);
        //选填-发送短信流水号
        //$request->setOutId("1234");

        //发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);
        $acsResponse = json_decode(json_encode($acsResponse),TRUE);

        if($acsResponse['Code'] == 'OK')
            $acsResponse['status'] = 1;
        else
            $acsResponse['status'] = 0;
        return $acsResponse;
    }

    /**
     * 邮件发送
     * @param $to    接收人
     * @param string $subject   邮件标题
     * @param string $content   邮件内容(html模板渲染后的内容)
     * @throws Exception
     * @throws phpmailerException
     */
    function send_email($to,$subject='',$content=''){
        $config = C('email');
        vendor('phpmailer.PHPMailerAutoload'); ////require_once vendor/phpmailer/PHPMailerAutoload.php';
        $mail = new PHPMailer;
        $mail->CharSet  = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;
        //调试输出格式
        //$mail->Debugoutput = 'html';
        //smtp服务器
        $mail->Host = $config['smtp_server'];
        //端口 - likely to be 25, 465 or 587
        $mail->Port = $config['smtp_port'];

        if($mail->Port === 465) $mail->SMTPSecure = 'ssl';// 使用安全协议
        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //用户名
        $mail->Username = $config['smtp_user'];
        //密码
        $mail->Password = $config['smtp_pwd'];
        //Set who the message is to be sent from
        $mail->setFrom($config['smtp_user']);
        //回复地址
        //$mail->addReplyTo('replyto@example.com', 'First Last');
        //接收邮件方
        if(is_array($to)){
            foreach ($to as $v){
                $mail->addAddress($v);
            }
        }else{
            $mail->addAddress($to);
        }

        $mail->isHTML(true);// send as HTML
        //标题
        $mail->Subject = $subject;
        //HTML内容转换
        $mail->msgHTML($content);
        //Replace the plain text body with one created manually
        //$mail->AltBody = 'This is a plain-text message body';
        //添加附件
        //$mail->addAttachment('images/phpmailer_mini.png');
        //send the message, check for errors
        return $mail->send();
    }

    /*
     * 二维码生成
     */
    public function qrcode($uid, $url) {
        include APP_PATH.'base/util/qrcode/phpqrcode.php';
        $errorCorrectionLevel = 'L'; //容错级别
        $matrixPointSize = 6; //生成图片大小
        $web_site = $this->get_website();
        // 生成二维码图片
        \QRcode::png($url, "public/img/qrcode/$uid.png", $errorCorrectionLevel, $matrixPointSize, 2);
        return $web_site."/public/img/qrcode/$uid.png";
    }

    /*
     * 获取网址
     */

    public function get_website() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $url = $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
        return $url;
    }




}
