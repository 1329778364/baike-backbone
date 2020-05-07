<?php

namespace app\common\model;

use app\common\controller\AlismsController;
use app\http\middleware\ApiUserAuth;
use app\lib\exception\BaseException;
use think\App;
use think\Db;
use think\Model;
use think\facade\Cache;

class User extends Model
{
    /*自动写入时间 注意特定字段为creat_time*/
    protected $autoWriteTimestamp = true;

    // 发送验证码 待完善
        //发送验证码
    public function sendCode(){
        // 获取用户提交手机号码
        $phone = request()->param('phone');
        // 判断是否已经发送过
        if(Cache::get($phone)) throw new BaseException(['code'=>200,'msg'=>'你操作得太快了','errorCode'=>30001]);
        // 生成4位验证码
        $code = random_int(1000,9999);
        // 判断是否开启验证码功能
        if(!config('api.aliSMS.isopen')){
            Cache::set($phone,$code,config('api.aliSMS.expire'));
            throw new BaseException(['code'=>200,'msg'=>'验证码：'.$code,'errorCode'=>30005]);
        }
        // 发送验证码
        $res = AliSMSController::SendSMS($phone,$code);
        //发送成功 写入缓存
        if($res['Code']=='OK') return Cache::set($phone,$code,config('api.aliSMS.expire'));
        // 无效号码
        if($res['Code']=='isv.MOBILE_NUMBER_ILLEGAL') throw new BaseException(['code'=>200,'msg'=>'无效号码','errorCode'=>30002]);
        // 触发日限制
        if($res['Code']=='isv.DAY_LIMIT_CONTROL') throw new BaseException(['code'=>200,'msg'=>'今日你已经发送超过限制，改日再来','errorCode'=>30003]);
        // 发送失败
        throw new BaseException(['code'=>200,'msg'=>'发送失败','errorCode'=>30004]);
    }


    /*手机号登录的逻辑*/
    public function phoneLogin(){
        /*获取用户发过来的所有参数*/
        $params = request()->param();
        /*判断用户是否已经注册*/
        $user = $this->isExist(['phone'=>$params["phone"]]);
        if (!$user){
            /*进行注册*/
            $user = self::create([
                'username'=>$params['phone'],
                'phone'=>$params['phone'],
//                'password'=>password_hash($params['phone'],PASSWORD_DEFAULT)
            ]);
            /*在用户信息表创建用户记录*/
            $user->userinfo()->create(['user_id'=>$user->id]);
            $user->logintype = 'phone';
            $userarr = $user->toArray();
            $userarr['token'] = $this->CreateSaveToken($userarr);
            $userarr['userinfo'] = $user->userinfo->toArray();
          	$userarr['email'] = false;
            $userarr['password'] = false;
         	return $userarr;
        }
        /*有用户 则检查用户是否被禁用*/
        $this->checkStatus($user->toArray());
        /*登录成功，返回token*/
        $userarr = $user->toArray();
        $userarr['token'] = $this->CreateSaveToken($userarr);
        $userarr['userinfo'] = $user->userinfo->toArray();
        $userarr['password'] = $userarr['password'] ? true : false;
        $userarr['logintype'] = "phone";

        return $userarr;
    }

    /*验证用户是否存在*/
    public function isExist($arr=[]){
        if(!is_array($arr)) return false;
        if (array_key_exists('phone',$arr)) { // 手机号码
            $user = $this->where('phone',$arr['phone'])->find();
            if ($user) $user->logintype = 'phone';
            return $user;
        }
        // 用户id
        if (array_key_exists('id',$arr)) { // 用户名
            return $this->where('id',$arr['id'])->find();
        }
        if (array_key_exists('email',$arr)) { // 邮箱
            $user = $this->where('email',$arr['email'])->find();
            if ($user) $user->logintype = 'email';
            return $user;
        }
        if (array_key_exists('username',$arr)) { // 用户名
            $user = $this->where('username',$arr['username'])->find();
            if ($user) $user->logintype = 'username';
            return $user;
        }
        // 第三方参数
        if (array_key_exists('provider',$arr)) {
            $where = [
                'type'=>$arr['provider'],
                'openid'=>$arr['openid']
            ];
            $user = $this->userbind()->where($where)->find();
            if ($user) $user->logintype = $arr['provider'];
            return $user;
        }
        return false;
    }

    // 用户是否被禁用
    public function checkStatus($arr,$isReget = false){
        $status = 1;
        if ($isReget) {
            // 账号密码登录 和 第三方登录
            $userid = array_key_exists('user_id',$arr)?$arr['user_id']:$arr['id'];
            // 判断第三方登录是否绑定了手机号码 在这里不强制绑定
            if ($userid < 1) return $arr;
            // 查询user表
            $user = $this->find($userid)->toArray();
            // 拿到status
            $status = $user['status'];
        }else{
            $status = $arr['status'];
        }
        if($status==0) throw new BaseException(['code'=>200,'msg'=>'该用户已被禁用','errorCode'=>20001]);
        return $arr;
    }

    // 生成并保存token
    public function CreateSaveToken($arr=[]){
        // 生成token
        $token = sha1(md5(uniqid(md5(microtime(true)),true)));
        $arr['token'] = $token;
        // 登录过期时间
        $expire =array_key_exists('expires_in',$arr) ? $arr['expires_in'] : config('api.token_expire');
        // 保存到缓存中
        if (!Cache::set($token,$arr,$expire)) throw new BaseException();
        // 返回token
        return $token;
    }

    /*用户密码登录*/
    public function login(){
        $params = request()->param();
        /*验证用户是否存在*/
        $user = $this->isExist($this->filteruserData($params['username']));
        if (!$user) throw new BaseException(['code'=>200,'msg'=>'昵称/邮箱/手机号错误','errorCode'=>20000]);

        $this->checkStatus($user->toArray());
        /*验证密码是否正确*/
        $this->checkPassword($params['password'], $user->password);

        return $this->CreateSaveToken($user->toArray()) ;

    }

    //判断用户民类型 验证用户名是什么格式，昵称/邮箱/手机号
    public function filterUserData($data){
        $arr=[];
        // 验证是否是手机号码
        if(preg_match('^1(3|4|5|7|8)[0-9]\d{8}$^', $data)){
            $arr['phone']=$data;
            return $arr;
        }
        // 验证是否是邮箱
        if(preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', $data)){
            $arr['email']=$data;
            return $arr;
        }
        $arr['username']=$data;
        return $arr;
    }

    /*验证密码是否正确*/
    public function checkPassword($password,$hash){
    if (!$hash) throw new BaseException(['code'=>200,'msg'=>'密码错误','errorCode'=>20002]);
    // 密码错误
    if(!password_verify($password,$hash)) throw new BaseException(['code'=>200,'msg'=>'密码错误','errorCode'=>20002]);
    return true;

    }

    /*第三方登录函数实现*/
     public function otherlogin(){
        // 获取所有参数
        $param = request()->param();
        // 解密过程（待添加）
        // 验证用户是否存在
        $user = $this->isExist(['provider'=>$param['provider'],'openid'=>$param['openid']]);
        // 用户不存在，创建用户
        $arr = [];
        if (!$user) {
            $user = $this->userbind()->create([
                'type'=>$param['provider'],
                'openid'=>$param['openid'],
                'nickname'=>$param['nickName'],
                'avatarurl'=>$param['avatarUrl'],
            ]);
            $arr = $user->toArray();
          	 $arr['user_id'] = 0;
            $arr['expires_in'] = $param['expires_in'];
            $arr['logintype'] = $param['provider'];
            $arr['token'] = $this->CreateSaveToken($arr);
            return $arr;
        }
         // 用户是否被禁用
        $arr = $this->checkStatus($user->toArray(),true);
        // 登录成功，返回用户信息+token
        $arr['expires_in'] = $param['expires_in'];
        $userarr = $user->toArray();
        $userarr['token'] = $this->CreateSaveToken($arr);
        // 判断是否绑定
        if ($user->user_id) {
            $currentuser = $this->find($user->user_id);
            $userarr['user'] = $currentuser->toArray();
            $userarr['user']['userinfo'] = $currentuser->userinfo->toArray();
            $userarr['user']['password'] = $userarr['user']['password'] ? true : false;
        }
        return $userarr;
    }


    // 绑定用户信息表
    public function userinfo(){
        return $this->hasOne('Userinfo');
    }

    /*绑定第三方登录*/
    public function userbind(){
        return $this->hasMany('UserBind');
    }

    /*验证是否绑定手机号*/
    public function OtherLoginIsBindPhone($user){
        if (array_key_exists('type',$user)){
            /*有type 表示第三方登录 需要检验是否绑定手机号*/
            if ($user['user_id']<1) TApiException(['请绑定手机号！',20008]);
        }
    }

    /*关联用户关注表*/
    public function withFollow(){
        return $this->hasMany("Follow");
    }


    /*退出登录的实现函数*/
    public function logout(){
        /*pull 获取并清除缓存*/
        if (Cache::pull(request()->userToken)){
            TApiException(['注销成功！',30006]);
            return true;
        }
    }

    // 关联文章
    public function post(){
        return $this->hasMany('Post');
    }

    /*获取指定用户下面的文章列表*/
    public function getPostList(){
        $param = request()->param();
        return $this->get($param["id"])->post()->with([
            "user"=>function($query){
                return $query->field('id,username,userpic');
            },
            'image'=>function($query){
                return $query->field('url');
            },
            "share"
        ])->where('isopen',1)->page($param["page"],10)->select();

    }

    public function getAllPostList(){
        $param = request()->param();
        $user_id = request()->userId;
        return $this->get($user_id)->post()->with([
            "user"=>function($query){
                return $query->field('id,username,userpic');
            },
            'image'=>function($query){
                return $query->field('url');
            },
            "share"
        ])->page($param["page"],10)->select();
    }

    public function Search(){
        $param = request()->param();
        return self::where('username', 'like', '%' . $param['keyword'] . "%")
            ->page($param["page"],10)->select();
    }

    public function editUserpic(){
        $param = request()->param();
        $userId = request()->userId;
        $image = (new Image())->upload($userId, 'userpic');
        $user = self::get($userId);
        $user->userpic = getFileUrl($image->url);
        if ($user->save()) return true;
        TApiException("无法修改");

    }

    /*修改用户信息*/
    public function editUserInfo(){
        $param = request()->param();
        $userId = request()->userId;

        /*修改用户名称*/
        $user = self::get($userId);
        $user->username = $param["name"];
        $user->save();

        /*修改用户基本信息*/
        $userinfo = $user->userinfo()->find();

        $userinfo->sex = $param["sex"];
        $userinfo->qg = $param["qg"];
        $userinfo->job = $param["job"];
        $userinfo->birthday = $param["birthday"];
        $userinfo->path = $param["path"];
        $userinfo->save();
        return $user->userinfo()->find();
    }

    // 修改密码
    public function repassword()
    {
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $user = self::get($userid);
        // 手机注册的用户并没有原密码,直接修改即可
        if ($user['password']) {
            // 判断旧密码是否正确
            $this->checkPassword($params['oldpassword'],$user['password']);
        }
        // 修改密码
        $newpassword = password_hash($params['newpassword'],PASSWORD_DEFAULT);
        $res = $this->save([
            'password'=>$newpassword
        ],['id'=>$userid]);
        if (!$res) TApiException('修改密码失败',20009,200);
        $user['password'] = $newpassword;
        // 更新缓存信息
        Cache::set(request()->Token,$user,config('api.token_expire'));

        return true;
    }

    /*关注用户 */
    public function toFollow(){
        $param = request()->param();
        $userId = request()->userId;
        $follow_id = $param["follow_id"];

        if ($follow_id == $userId) TApiException("请不要关注自己",10000,200);

        $followModel = self::get($userId)->withFollow();
        $follow = $followModel->where('follow_id', $follow_id)->find();
//        halt($follow);
        if ($follow) TApiException("已经关注", 10000,200);
        $followModel->create([
            "user_id" => $userId,
            "follow_id"=>$follow_id
        ]);
        return true;
    }

    /*取消关注用户 */
    public function ToUnFollow(){
        $param = request()->param();
        $userId = request()->userId;
        $follow_id = $param["follow_id"];

        if ($follow_id == $userId) TApiException("无法取消关注自己");

        $followModel = self::get($userId)->withFollow();
        $follow = $followModel->where('follow_id', $follow_id)->find();
        if (!$follow) TApiException("没有关注");

        $follow->delete();
        return true;
    }

    // 关联粉丝列表·
    public function fens(){
        return $this->belongsToMany('User','Follow','user_id','follow_id');
    }

    // 关联关注列表
    public function follows(){
        return $this->belongsToMany('User','Follow','follow_id','user_id');
    }

    /*获取互相关注列表*/
    public function getFriendsList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $page = $params['page'];

        $follows = Db::table("user")->where("id", 'IN', function ($query) use ($userid) {
            $query->table('follow')
                ->where('user_id', 'IN', function ($query) use ($userid) {
                    $query->table('follow')->where('user_id', $userid)->field('follow_id');  /*找到被我关注的follow_id*/
                })->where('follow_id', $userid)/*找到这些follow_id的关注对象 如果其中有我的user_id 那就表明互关*/
                ->field('user_id');
        })->field('id,username,userpic')->page($page,10)->select();

        return $follows;
    }



    /*获取粉丝列表 关注我的*/
    public function getFensList(){
        $params = request()->param();
        $userid = request()->userId;
        $page = $params['page'];
        /*第一种方法*/
        $fens = Db::table("user")->where("id", "IN", function ($query) use ($userid) {
        $query->table("follow")->where(["follow_id" => $userid])->field("user_id");
        })->field("id,username,userpic")->page($page,10)->select();

        return $fens;
    }

    /*获取关注列表*/
    public function getFollowsList(){
        $params = request()->param();
        $userid = request()->userId;
        $page = $params['page'];

        $follows = Db::table("user")->where("id", "IN", function ($query) use ($userid) {
                $query->table("follow")->where(["user_id" => $userid])->field("follow_id");
            })->field("id,username,userpic")->page($page, 10) -> select();
        return $follows;
    }

     // 关联评论
    public function comments(){
        return $this->hasMany('Comment');
    }

    // 关联今日文章
    public function todayPosts(){
        return $this->hasMany('Post')->whereTime('create_time','today');
    }

      // 关联粉丝（关联到follow表）
    public function withfen(){
        return $this->hasMany('Follow','follow_id');
    }

    // 统计获取用户相关数据（总文章数，今日文章数，评论数 ，关注数，粉丝数，文章总点赞数，好友数）
    public function getCountsFunc(){
        // 获取用户id
        $userid = request()->param('user_id');
        $user = $this->withCount(['post','comments','todayPosts','withfollow','withfen'])->find($userid);
        if (!$user) TApiException();
        // 获取当前用户发布的所有文章id
        $postIds = $user->post()->field('id')->select();
        foreach ($postIds as $key => $value) {
            $arr[] = $value['id'];
        }
        if (!isset($arr)) $arr = 0;
        $count = \Db::name('support')->where('type',1)->where('post_id','in',$arr)->count();

      	// 获取好友数
        $friendCounts = \Db::table('follow')
        ->where('user_id', 'IN', function ($query) use($userid){
            // 找出所有我关注的人的用户id
            $query->table('follow')->where('user_id', $userid)->field('follow_id');
        })->where('follow_id',$userid)
        ->count();

        return [
            "post_count"=>$user['post_count'],
            "comments_count"=>$user['comments_count'],
            "today_posts_count"=>$user['today_posts_count'],
            "withfollow_count"=>$user['withfollow_count'],
            "withfen_count"=>$user['withfen_count'],
            "total_ding_count"=>$count,
          	"friend_count"=>$friendCounts
        ];
    }

}
