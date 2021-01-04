<?php
//第三方配置
return [
    //jwt配置开始
    'jwt' => [
        //用于生成签名的密钥
        'key' => 'TEST_JWT_TOKEN',
        //access_token有效期，暂定为4小时，可根据业务调整时间
        'access_exp_time'  => 3600 * 4,
        //refresh_token有效期，暂定为15天，可根据业务调整时间
        'refresh_exp_time' => 86400 * 15
    ],

    //其它


];