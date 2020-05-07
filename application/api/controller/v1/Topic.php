<?php

namespace app\api\controller\v1;

use app\common\validate\TopicClassValidate;
use think\Controller;
use think\Request;
use app\common\controller\BaseController;
use app\common\model\Topic as TopicModel;


class Topic extends BaseController
{

    /*获取十个话题*/
    public function index()
    {
        $topics = (new TopicModel())->gethotlist();
        return self::showResCode('获取成功', ["list" => $topics]);

    }

    /*获取指定话题下面的文章列表*/
    public function post(){
        (new TopicClassValidate())->goCheck();
        $list = (new TopicModel())->getPost();
        return self::showResCode("提取成功",['list'=>$list]);
    }



}
