<?php

namespace app\common\controller;

use think\Controller;
use think\Request;

class BaseController extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    //api返回结果
    static public function showResCode($msg='',$data = [], $code=200)
    {
        $res = [
            'msg'=> $msg,
            'data'=> $data
        ];
        return json($res,$code);
    }

    //api返回无数据结果
    static public function showResCodeWithOutData($msg='未知',$code=200){
        return self::showResCode($msg,[],$code);
    }

}
