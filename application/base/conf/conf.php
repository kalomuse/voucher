<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/8/22
 * Time: 上午11:22
 */
return array(
    'message' => array(
        'appKey' => 'LTAIafDzVAWHR0Di',
        'secretKey' => 'CdaAZb5kp2rxNC8QQwepa5xBWUNa10',
        'templateCode' => 'SMS_75760128',
        'smsSign' => '恋爱77',

    ),
    'email' => array(
        'smtp_server' => 'smtp.qq.com',
        'smtp_port' => 25,
        'smtp_user' => '870947386@qq.com',
        'smtp_pwd' => 'nrvvhfkfnczbbajj',
    ),
    'alipay' => array(
        'nofify_url'=> SITE_URL.'/mobile/pay/ali_callback',
        'appId' => "2017071107721020",
        'rsaPrivateKey' => 'MIICWwIBAAKBgQCx6T8iphiu24fIlN5IcORa1o7QeJCKr1keN+TdGeLl7cLiDh2c5r46Rp+oRXoZ1v9lxK7Vyhpwa6ixqHazKtqeboAGPeH13fXfoy3gvD0LLvvjdDveVLv2dz5MV4jxFgPnRNO1NScMPaJfBleZNPYej++tAJWaY3utezM4zx81XwIDAQABAoGAaGMX5UeZkfdDC2C1r8F1awsbDpy/CnBqFL3s7gIDTK+dui9YFriYgu7vPLdPNhwdDGRDxTLpIm84sszKoMOTM4kwtFD5ocedUP4rdBGzKsxgql5vzQwAFUyizgCuUmZkI7y0xQPNxvJawG0nyPdkAJFeuE8IpjJtm9nxAJt38AECQQDmLBzAQCJYdU3DPMN0J/CR+4otuF3evDp5owK5jZTtFplicM1yIvsGWqlIwEqU1vIabAxeDFfLbl8Wn0R0Zh1fAkEAxd/jioV3j2NQFRQw6CPTtR/+UErZoDhGOMOlyWLvvqgebkvpiRcuukgEgzJn1FtHFG7TMzkyHb35ToCyisXoAQJAR2ajvJeoj9xDtS7iSuQg4ogvQyOQKwok8Zq5u6nJ6wo5pqnrcV6clEoHfYP5HtbW349o/rvBeF/Sq4fYimsq7QJAMCne6Miz4WL7CyZvARI3Zc7zx/dwIV+ROB/nKq26TV3+ijpQDd5msVD2SDjsrPPKyV5wafdyC2tCU6lfzAeAAQJAPyqIhnaqU0Hsrak4Y3R0PWBjh+0CEyQTylZF10FVoHBdSmo3lDO4TPybvzTvsNhxezaQwt40kzlRRxYmzCTEHg==',
        'alipayrsaPublicKey' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCx6T8iphiu24fIlN5IcORa1o7QeJCKr1keN+TdGeLl7cLiDh2c5r46Rp+oRXoZ1v9lxK7Vyhpwa6ixqHazKtqeboAGPeH13fXfoy3gvD0LLvvjdDveVLv2dz5MV4jxFgPnRNO1NScMPaJfBleZNPYej++tAJWaY3utezM4zx81XwIDAQAB',
    ),
    'wx' => array(
        'appid' => '',
        'appsecret' => '',
        'alias' => 'jiashanquanzi',
        'token' => 'KALOMUSELAIGEFEIJI',
        'AESkey' => '3ap9p4NElKc20fGd3Qr6LApJmkY7cO46QRWRR5UmYRI',
    ),
    'wx_pay' => array(
        'appid' => '',
        'appsecret' => '',
    ),
    'wxpay' => array( //plugins/weixin/lib/WxPay.Config.php
        'nofify_url'=> SITE_URL.'/mobile/pay/wx_callback',
    ),
    'redis' => array(
        'ip' => '127.0.0.1',
        'port' => '6379',
    ),
    'baidumap' => array(
    'ak' => '4Bmo82NDk5BNrGqt9F0Bxq3GZrx3hsSg',
    'sk' => 'V8lGWxz609B8uBMWziXr2CLCE8WBM9d4',

    ),
);