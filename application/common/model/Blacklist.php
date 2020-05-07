<?php

namespace app\common\model;

use think\Model;

class Blacklist extends Model
{
    protected $autoWriteTimestamp = true;

    /*拉黑用户*/
    public function addBlack(){
        $param = request()->param();
        $blackId = $param['id'];
        $userId = request()->userId;

        if ($blackId == $userId) TApiException("不能拉黑自己");
        $arr = ["black_id" => $blackId,
                "user_id" => $userId];

        if (self::where($arr)->find()) TApiException("对方已经被拉黑");
        if (!self::create($arr)) TApiException("拉黑失败");
        return true;

    }

    /*取消拉黑*/
    public function removeBlack(){
        $param = request()->param();
        $userId = request()->userId;
        $black_id = $param["id"];

        if ($black_id == $userId) TApiException("非法操作,不能拉黑自己");

        $arr = [
            'user_id' => $userId,
            "black_id" => $black_id,
        ];

        $item = self::get($arr);
        if ($item){
            $item->delete();
            return true;
        }
        TApiException("拉黑记录不存在");

    }
}
