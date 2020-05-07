<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;
use app\common\validate\SupportValidate;
use app\common\model\Support as SupportModel;
use think\Controller;
use think\db\Where;
use think\Request;

class Support extends BaseController
{

   public function index(){
       (new SupportValidate())->goCheck();
       $list = (new SupportModel())->UserSupportPost();
       return self::showResCode("获取成功", ["list" => $list]);
   }

}
