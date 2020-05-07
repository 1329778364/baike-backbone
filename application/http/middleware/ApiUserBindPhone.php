<?php

namespace app\http\middleware;

use app\common\model\User;

class ApiUserBindPhone
{
    public function handle($request, \Closure $next)
    {
        $param = $request->userTokenUserInfo;
        (new User())->OtherLoginIsBindPhone($param);
        return $next($request);
    }
}
