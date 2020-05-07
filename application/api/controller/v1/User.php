<?php
namespace app\api\controller\v1;

use app\common\controller\BaseController;
use app\common\validate\BaseValidate;
use app\common\validate\UserValidate;
use app\common\model\User as UserModel;
use app\common\model\Follow as FollowModel;

class User extends BaseController
{
    /*发送验证码*/
    public function sendCode()
    {
        /*验证参数*/
        (new UserValidate())->goCheck('sendCode');
        (new UserModel())->sendCode();

        return self::showResCodeWithOutData('发送成功');
    }

    /*手机号登录*/
    public function phoneLogin(){
        /*验证合法性*/
        (new UserValidate())->goCheck('phonelogin');
        /*进行手机登录*/
        $user = (new UserModel())->phoneLogin();

        return self::showResCode('登录成功', $user);
    }

    /*用户登录接口*/
    public function login(){
        /*验证登录信息是否合法*/
        (new BaseValidate())->goCheck('login');
        /*验证登录*/
        $token = (new UserModel())->login();
        return self::showResCode('登录成功',['token'=>$token]);
    }

    /*第三方登录*/
    public function otherLogin(){
        // 验证登录信息
        (new UserValidate())->goCheck('otherlogin');
        $user = (new UserModel())->otherlogin();
        return self::showResCode('登录成功',$user);
    }

    public function getCounts(){
        (new UserValidate())->goCheck('getuserinfo');
        $user = (new UserModel())->getCountsFunc();
        return self::showResCode("获取统计数据成功",$user);
    }

    /*退出登录*/
    public function logout(){
        /*检验token合法性，用户是否已经登录 采用中间件 通过之后执行下面的退出操作*/
        (new UserModel())->logout();
        return '退出成功';
    }

    /*获取用户发布的文章 其他用户查看该用户发表的文章*/
    public function post(){
        (new UserValidate())->goCheck('post');
        $list = (new UserModel())->getPostList();
        return self::showResCode("获取用户文章列表成功",["list"=>$list]);
    }

    /*用户自己查看自己发布的文章*/
    public function Allpost(){
        (new UserValidate())->goCheck('allpost');
        $list = (new UserModel())->getAllPostList();
        return self::showResCode("获取自己的文章列表成功",["list"=>$list]);
    }

    /*修改用户头像*/
    public function editUserpic(){
        (new UserValidate())->goCheck("edituserpic");
        (new UserModel())->editUserpic();
        return self::showResCode("修改成功");
    }

    /*修改用户信息*/
    public function editUserInfo(){
        (new UserValidate())->goCheck("edituserinfo");
        $data = (new UserModel())->editUserInfo();
        return self::showResCode("修改成功",$data);

    }
    /*修改密码*/
    public function rePassword(){
        (new UserValidate())->goCheck('repassword');
        $data = (new UserModel())->repassword();
        return self::showResCode('修改密码成功',$data);
    }

    /*关注用户*/
    public function follow(){
        (new UserValidate())->goCheck("follow");
        (new UserModel())->toFollow();
        return self::showResCodeWithOutData('关注成功');
    }

    // 取消关注
    public function unfollow(){
        (new UserValidate())->goCheck("unfollow");
        (new UserModel())->ToUnFollow();
        return self::showResCodeWithOutData('取消关注成功');
    }

    // 互关列表
    public function friends(){
        (new UserValidate())->goCheck('getfriends');
        $list = (new UserModel())->getFriendsList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

    // 粉丝列表
    public function fens(){
        (new UserValidate())->goCheck('getfens');
        $list = (new UserModel())->getFensList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

    // 关注列表
    public function follows(){
        (new UserValidate())->goCheck('getfollows');
        $list = (new UserModel())->getFollowsList();
        return self::showResCode('获取成功',['list'=>$list]);
    }

}
