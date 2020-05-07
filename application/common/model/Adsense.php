<?php

namespace app\common\model;

use think\Cache;
use think\Model;

class Adsense extends Model
{
    public  function  getList()
    {
        $param = request()->param();
        return $this->where('type', $param["type"])->select();
    }
}
