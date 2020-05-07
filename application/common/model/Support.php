<?php

namespace app\common\model;

use think\Model;

class Support extends Model
{
    protected $autoWriteTimestamp = true;

    public function UserSupportPost(){
        $param = request()->param();
        $userId = request()->userId;
        /*首先 判断该用户是否已经点赞*/
        $support = $this->where(['user_id' => $userId, 'post_id' => $param["post_id"]])->find();
        if($support){
            if ($support["type"] == $param["type"]) TApiException("请勿重复操作",4000,200);
            return self::update(["id" => $support['id'], "type" => $param["type"]]);
        }

        /*如果没有则直接创建*/
        return self::create([
            'user_id' => $userId,
            'post_id' => $param["post_id"],
            'type'=>$param['type']
        ]);

    }
}
