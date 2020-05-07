<?php

namespace app\common\model;

use think\Model;

class Comment extends Model
{
    protected $autoWriteTimestamp = true;

    // 关联用户
    public function user(){
        return $this->belongsTo('User','user_id');
    }

    public function postComment()
    {
        /*对文章发表评论*/
        $param = request()->param();
        $userId = request()->userId;
        $comment = self::create([
            'user_id' => $userId,
            "fid" => $param['fid'],
            "data" => $param['data'],
            "post_id" => $param['post_id']
        ]);
        if ($comment){
            if ($param['fid'] > 0) { /* 表示回复一条评论 */
                $fcomment = self::get($param["fid"]); /*获取该评论的信息*/
                $fcomment->fnum = ['inc', 1]; /*将该评论的回复数增加一*/
                $fcomment->save();
            }
            return true;
        }
        TApiException("评论失败");
    }

}
