<?php

namespace app\common\model;

use think\Model;

class PostClass extends Model
{
    /*获取所有文章分类*/
    public function getPostClassType(){
        return $this->field('id,classname')->where('status',1)->select();
    }

    public function post(){
        return $this->hasMany("Post");
    }

    /*获取指定分类下面的文章*/
    public function getPost(){
        $param = request()->param();
        $userId = request()->userId;
        return self::get($param['id'])->post()->with([
            "user"=>function($query) use($userId){
                return $query->field('id,username,userpic')->with([
                    'fens'=>function($query) use($userId){
                        return $query->where('user_id',$userId)->hidden(['password',"pivot"]);
                    },
                  	'userinfo'
                ]);
            },
            'image'=>function($query){
                return $query->field('url');
            },
            "share",
            'support'=>function($query) use($userId){
                return $query->where('user_id', $userId);
            }
        ])->withCount(['Ding','Cai','comments'])->page($param['page'],10)->order('create_time','desc')->select();
    }
}
