<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
use app\mobile\service\OrderService;

require_once dirname(dirname(__FILE__))."/lib/WxPay.Config.php";
require_once dirname(dirname(__FILE__))."/lib/WxPay.Api.php";
require_once dirname(dirname(__FILE__))."/lib/WxPay.Notify.php";

$f = dirname(dirname(__FILE__));
//初始化日志
class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id, $isapp=0)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input, 6, $isapp);
        \think\Log::write('微信校验数据');
		\think\Log::write($result);
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        \think\Log::write('微信回调数据');
        \think\Log::write($data);
        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }

        \think\Log::write('微信自定义函数调用');
        $order_service = new OrderService();
        $order_service->pay_order($data);
        return true;
    }
}

//Log::DEBUG("begin notify");
//$notify = new PayNotifyCallBack();
//$notify->Handle(false);
