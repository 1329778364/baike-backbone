<?php

namespace app\api\controller\v1;

use think\Request;
use app\common\model\PostClass as PostClassModel;
use app\common\controller\BaseController;
use app\common\validate\TopicClassValidate;

class PostClass extends BaseController
{
    public function index()
    {
        $list = (new PostClassModel())->getPostClassType();
        return self::showResCode('获取成功', ["list" => $list]);
    }

    /*获取指定文章分类下的文章*/
    public function post(){
        (new TopicClassValidate())->goCheck();
        $list =  (new PostClassModel())->getPost();
        return self::showResCode('成功获取指定文章分类下的文章',['list'=>$list]);
    }


}
