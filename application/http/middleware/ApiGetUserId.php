<?php

namespace app\http\middleware;

use think\facade\Cache;

class ApiGetUserid
{
    public function handle($request, \Closure $next)
    {
        // 获取头部信息
        $param = $request->header();
        if (array_key_exists('token',$param)){
            $user = \Cache::get($param['token']);
            if ($user) {
                /*表示在userBInd表中的id*/
              	if(array_key_exists('type',$user)){
                	$request->userId = array_key_exists('user_id',$user) ? $user['user_id'] : 0;
                }else{
              	    /*表示直接就是user表登录方式*/
                	$request->userId = $user['id'];
                }
            }
        }
        return $next($request);
    }
}
