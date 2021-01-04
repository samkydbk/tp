<?php
namespace app\index\controller;

use think\Request;
use traits\Jwt;
use think\Response;

class Login {

    use Jwt;

    /**
     * 验证登录正确后，发放token
     * @param Request $request
     * @return \think\response\Json
     */
    public function index(Request $request)
    {
        $name = input('name');
        $password = input('password');
        //检查用户名密码逻辑完成
        //发放token
        //用户的数据
        $data = ['id' => 1, 'name' => $name];
        
        session_set_cookie_params(['samesite'=>'None']); 
        session('userInfo', $this->createToken($data));

        return json($this->createToken($data));
    }

    /**
     * 返回刷新后的access_token和refresh_token
     * @param Request $request
     * @return \think\response\Json
     */
    public function refreshToken(Request $request)
    {
        try {
            $refresh_token = input('refresh_token');
            //获取刷新token负载信息
            $payload = $this->verifyToken($refresh_token);
            //获取新的access_token和refresh_token
            return json($this->createToken($payload));
        } catch (\Exception $e) {
            //如果refresh_token过期，刚返回状态给前端
        }

    }

    /**
     * 获取api业务接口数据
     * @param Request $request
     * @return \think\response\Json
     */
    public function getApiData(Request $request)
    {
        try {
            $access_token = input('access_token');
            //获取刷新token负载信息
            $payload = $this->verifyToken($access_token);
            //根据负载中用户信息，获取对应业务数据
            return json($payload);
        } catch (\Exception $e) {
            //如果refresh_token过期，刚返回状态给前端
        }
    }

    public function getUserInfo()
    {
        session_set_cookie_params(['samesite'=>'None']);
        return session('userInfo');
    }

}
