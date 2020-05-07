<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;
use think\Controller;
use think\Request;
use app\common\validate\BlacklistValidate;
use app\common\model\Blacklist as BlacklistModel;

class Blacklist extends BaseController
{

    // 加入黑名单
    public function addBlack(){
        (new BlacklistValidate())->goCheck();
        (new BlacklistModel())->addBlack();
        return self::showResCodeWithOutData('加入黑名单成功');
    }

    public function removeBlack(){
        (new BlacklistValidate())->goCheck();
        (new BlacklistModel())->removeBlack();
        return self::showResCodeWithOutData('取消拉黑成功');
    }

}
