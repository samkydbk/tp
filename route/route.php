<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//测试
//用户登录
Route::post('login','index/Login/index');
//刷新token
Route::post('refreshtoken', 'index/Login/refreshToken');
//获取业务api数据
Route::post('getapidata', 'index/Login/getApiData');

Route::get('getuserinfo', 'index/Login/getUserInfo');

//rabbitmq
Route::get('sendmsg', 'index/Send/sendMsg');
Route::get('receivemsg', 'index/Receive/receiveMsg');
Route::get('rabbitmq', 'index/Rabbitmq/start');

//



