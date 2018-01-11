<?php
namespace app\base\controller;
use think\Controller;
use app\base\service;
require_once APP_PATH."base/service/Util.php";

class Common extends Controller
{

    public function send_message() {
        $util = new service\Util();
        $res = $util->send_validate_code();
        Header('Content-type:application/json; charset=UTF-8');
        return exit(json_encode($res));
    }
}

