<?php

namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\User as UserModel;

class Post extends Model
{
    protected $autoWriteTimestamp = true;

    /*关联图片表*/
    public function images(){
        return $this->belongsToMany('Image','post_image');
    }

    public function user(){
        return $this->belongsTo("User","user_id","id");
    }

    public function image(){
        return $this->belongsToMany("Image","post_image");
    }

    /*一篇文章可以被多个人分享*/
    public function share(){
        return $this->belongsTo('Post',"share_id","id");
    }

    /*关联 文章的顶踩信息*/
    public function support(){
        return $this->hasMany('Support');
    }
    // 关联话题表
    public function topics(){
        return $this->belongsToMany('Topic','topic_post');
    }

  	// 关联顶数
    public function Ding(){
        return $this->hasMany('Support')->where('type',0);
    }

    // 关联踩数
    public function Cai(){
        return $this->hasMany('Support')->where('type',1);
    }

    // 绑定用户信息表
    public function userinfo(){
        return $this->hasOne('Userinfo',"user_id","user_id");
    }

    /*关联用户关注列表 查看文章是否已经关注*/
    public function follow()
    {
        return $this->hasMany('Follow',"follow_id","user_id");
    }

    /*关联评论信息*/
    public function comments()
    {
        return $this->hasMany('Comment');
    }


    /*发布文章*/
    public function createPost(){
        // 获取所有参数
        $params = request()->param();
        $userModel = new User();
        // 获取用户id
        $user_id=request()->userId;
        $currentUser = $userModel->get($user_id);
        $path = $currentUser->userinfo->path;
        // 发布文章
        $title = mb_substr($params['text'],0,30);
        $post = $this->create([
            'user_id'=>$user_id,
            'title'=>$title,
            'titlepic'=>'',
            'content'=>$params['text'],
            'path'=>$path ? $path : '未知',
            'type'=>0,
            'post_class_id'=>$params['post_class_id'],
            'share_id'=>0,
            'isopen'=>$params['isopen']
        ]);
        // 关联图片
        $imglistLength = count($params['imglist']);
        if($imglistLength > 0){
            $ImageModel = new Image();
            $imgidarr = [];
            for ($i=0; $i < $imglistLength; $i++) {
                if ($ImageModel->isImageExist($params['imglist'][$i]['id'],$user_id)) {
                    $imgidarr[] = $params['imglist'][$i]['id'];
                }
            }
            // 发布关联
            if(count($imgidarr)>0) $post->images()->attach($imgidarr,['create_time'=>time()]);
        }
        // 返回成功
        return true;

    }

    public function getPostDetail(){
        $param = request()->param();

        /*由于用户与文章之间是一对多的关系 我们要从文章获取用户的信息 则用BelongsTo 来查询文章归属于那个用户 属于多对一*/
        return $this->with([
            'user'=>function($query){
                return $query->field("id,username,userpic");
            },
            "image"=>function($query){
                return $query->field("url")->hidden(['pivot']);
            },
            "share"=>function($query){
                return $query;
            },
            "userinfo"=>function($query){
                return $query;
            }

        ])->find($param["id"]);
    }

    /*搜索文章*/
    public function Search(){
        $param = request()->param();
        $userId = request()->userId?request()->userId:0;
        /*法一*/
//        $posts = self::where('title', 'like', '%' . $param["keyword"] . "%")->page($param["page"])->select();
//        $arr = [];
//        for($i=0;$i<count($posts);$i++){
//            $arr[] = $posts[$i]->with([
//                "user"=>function($query) use($userId){
//                    return $query->field("id,username,userpic")->with([
//                        "fens"=>function($query) use($userId){
//                            return $query->where("user_id",$userId)->hidden(["password","pivot"]);
//                        },
//                        "userinfo"
//                    ]);
//                },
//                "image"=>function($query){
//                    return $query->field("url")->hidden(["pivot"]);
//                },
//                "share",
//                "support"=>function($query) use($userId){
//                    return $query->where("user_id", $userId);
//                }
//            ])->withCount(["Cai", "Ding", "comments"])->get($posts[$i]->id)->toArray();
//        }
////        return $arr;
        /*法2*/
        return self::where('title', 'like', '%' . $param["keyword"] . "%")->with([
            "user"=>function($query) use($userId){
                return $query->field("id,username,userpic")->with([
                    "fens"=>function($query) use($userId){
                        return $query->where("user_id",$userId)->hidden(["password", "pivot"]);
                    },
                    "userinfo"
                ]);
            },
            "image"=>function($query){
                return $query->field("url")->hidden(["pivot"]);
            },
            "share",
            /*文章的统计信息*/
            "support"=>function($query) use($userId){
                return $query->where("user_id", $userId);/*得到该用户的操作信息*/
            }
        ])->withCount(["Ding","Cai","comments"])->page($param["page"],10)->order("create_time","desc")->select();
    }


    public function getComment(){
        $param = request()->param();

        return self::get($param["id"])->comments()
            ->with([
            'user'=>function($query){
                return $query->field('id,username,userpic');
            }
        ])->select();
    }
}
