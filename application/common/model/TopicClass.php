<?php

namespace app\common\model;

use think\Model;

class TopicClass extends Model
{
    public function getTopicClassList(){
        return $this->field('id,classname')->where("status", 1)->select()->toArray();
    }

    /*话题关联表 使得话题分类的id 能够关联到话题表对应的类型*/
    public  function topic(){
        /*采用一对多关联 一个话题分类下面有许多的话题数据*/
        return $this->hasMany('Topic');
    }

    public function getTopic(){
        $param = request()->param();
        return self::get($param['id'])->topic()->page($param["page"],10)->select();
    }

}
