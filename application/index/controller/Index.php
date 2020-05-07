<?php

namespace app\index\controller;
use app\common\controller\BaseController;

class Index extends BaseController
{

    public function index()
    {
//         (new CeshiValidate())->goCheck('login');
//         throw new BaseException(['code'=>400,'msg'=>'验证失败']);
        $list = [
            ['id'=>10, 'title'=>'123'],
            ['id'=>11, 'title'=>'124']        
        ];
        return self::showResCode('获取11成功!', ['list'=>$list]);
    }

}
