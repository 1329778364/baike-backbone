<?php

namespace app\api\controller\v1;

use think\Request;
use app\common\controller\BaseController;
use app\common\model\TopicClass as TopicClassModel;
use app\common\validate\TopicClassValidate;

class TopicClass extends BaseController
{

    public function index()
    {
        $list = (new TopicClassModel())->getTopicClassList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

    /*获取指定话题分类下面的话题列表*/
    public function topic(){
        (new TopicClassValidate())->goCheck();
        /*验证传过来的参数*/

        /*获取数据*/
        $list = (new TopicClassModel())->getTopic();
        return self::showResCode("获取成功", ["list"=>$list]);

    }

}
