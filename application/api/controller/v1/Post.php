<?php

namespace app\api\controller\v1;

use app\common\validate\PostValidate;
use think\Controller;
use think\Request;
use app\common\model\Post as PostModel;
use app\common\controller\BaseController;
use app\common\validate\TopicClassValidate;

/* 用于文章发布 */

class Post extends BaseController
{
    /*发布文章*/
    public function create()
    {
        /*校验文章的内容*/
        (new PostValidate())->goCheck('create');
        (new PostModel())->createPost();
        return self::showResCodeWithOutData('发布成功');
    }

    /*获取文章详情*/
    public function index(){
        (new PostValidate())->goCheck("detail");
        $detail = (new PostModel())->getPostDetail();
        return self::showResCode("获取成功",["detail"=>$detail]);
    }


    public function comment(){
        (new PostValidate())->goCheck("comment");
        $detail = (new PostModel())->getComment();
        return self::showResCode("获取成功",["detail"=>$detail]);
    }


}
