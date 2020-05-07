<?php

namespace app\common\model;

use think\Model;

class Update extends Model
{
    protected $autoWriteTimestamp = true;

    public function appUpdate()
    {
        $version = request()->param('ver');
        $res = self::where("status", 1)->order("create_time", 'desc')->find();
        if (!$res) TApiException("暂无版本更新！");
        if ($version == $res["version"]) TApiException("已经是最新版");
        if ($version<$res["version"]) TApiException("点击获取更新");
        return $res;
    }
}
