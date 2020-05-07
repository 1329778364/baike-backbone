<?php

namespace app\http\middleware;

use app\lib\exception\BaseException;
use http\Client\Curl\User;
use think\Cache;

class ApiUserAuth
{
    public function handle($request, \Closure $next)
    {
        $params = $request->header();
        if (!array_key_exists("token", $params)) TApiException("非法token，禁止操作",20003,200);
        $token = $params["token"];
        $user = \Cache::get($token);

        if (!$user) TApiException("非法token，请重新登录",20003,200);
        $request->userToken=$token;
        $request->userId = array_key_exists('type',$user)?$user['user_id']:$user['id'];
        $request->userTokenUserInfo = $user;

        return $next($request);
    }
}
