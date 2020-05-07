<?php

namespace app\common\model;

use think\Model;

class Feedback extends Model
{
    protected $autoWriteTimestamp = true;

    public function feedback(){
        $params = request()->param();
        $userid = request()->userId;
        $data = $params["data"];
        $to_id = 1; /*客服编号*/
        $arr = [
            'to_id' => $to_id,
            'from_id' => $userid,
            'data' => $data,
        ];
        if (!self::create($arr)) TApiException();
        return true;
    }

    // 获取用户反馈列表
    public function feedbacklist(){
        $page = request()->param('page');
        $user_id = request()->userId;
        return self::where('from_id',$user_id)->whereOr("to_id",$user_id)->page($page,10)->order('create_time','desc')->select();

    }
}
