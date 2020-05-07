<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;
use app\common\validate\AdsenseValidate;
use think\Controller;
use think\Request;
use app\common\model\Adsense as AdsenseModel;


class Adsense extends BaseController
{
    public function index(){
        (new AdsenseValidate())->goCheck();
        $list = (new AdsenseModel())->getList();
        return self::showResCode('获取广告数据成功', ['list'=>$list]);
    }
}
