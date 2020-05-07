<?php

namespace app\common\model;

use http\Exception\BadHeaderException;
use think\Db;
use think\Model;

class Topic extends Model
{
    /*获取热门话题列表*/
    public function gethotlist(){
        return $this->where('type',1)->withCount(['post','todaypost'])->limit(10)->select()->toArray();
    }

    // 关联今日文章
    public function todaypost()
    {
        return $this->belongsToMany('Post','topic_post')->whereTime('post.create_time', 'today');
    }

    // 关联文章
    public function post(){
        return $this->belongsToMany('Post','topic_post');
    }

    /*获取指定话题下面的文章列表 分页*/
    public function getPost(){
        // 获取所有参数
        $param = request()->param();
        /*当前用户id*/
        $userId = request()->userId?request()->userId:0;
        /*得到10条文章数据包含 其id用于获取统计好的数据*/
        $posts = self::get($param['id'])->post()->page($param['page'],10)->select();
        $arr = [];
        for ($i=0; $i<count($posts); $i++){

            $arr[] = Post::with([
            'user'=>function($query) use($userId){
                return $query->field('id,username,userpic')->with([
                    /* 获取该文章用户的粉丝列表 查看访问者是否已经关注该文章用户 */
                    "fens"=>function($query) use($userId) {
                        return $query->where("user_id", $userId)->hidden(["password"]);
                    },
                    /*获取该文章的用户信息*/
                    "userinfo"
                ]);
                },
            /* 获取该文章的图片*/
            'images'=>function($query){
                return $query->field('url')->hidden(["pivot"]);
                },
            'share',
            /*查看访问者对该文章的顶踩操作*/
            "support"=>function($query) use($userId){
                return $query->where("user_id", $userId);
            }])->withCount(['Ding','Cai','comments'])->get($posts[$i]->id)->toArray();
        }
        return $arr;
    }


    public function Search(){
        $param = request()->param();
        return self::where('title', "like", "%" . $param['keyword'] . "%")
            ->page($param['page'],10)->select();
    }
}
