<?php

namespace app\api\controller\v1;

use think\Request;
use app\common\controller\BaseController;
use app\common\model\Image as ImageModel;

class Image extends BaseController
{

    public function uploadmore()
    {
        $list = (new ImageModel())->uploadMore();
        return self::showResCode("上传成功！",['list'=>$list]);
    }

}
