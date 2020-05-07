<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
//
Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

//不需要token验证的路由分组
Route::group('api/:version/',function (){
    /*发送验证码*/
    Route::post('user/sendcode','api/:version.User/sendCode');
    /*手机号登录*/
    Route::post('user/phonelogin','api/:version.User/phoneLogin');
    /*用户登录*/
    Route::post('user/login','api/:version.User/login');
    /*第三方登录*/
    Route::post('user/otherlogin','api/:version.User/otherLogin');

    /*获取文章分类*/
    Route::get('postclass','api/:version.PostClass/index');
    /*获取话题分类列表*/
    Route::get('topicclass','api/:version.TopicClass/index');
    /*获取话题列表*/
    Route::get('hottopic','api/:version.Topic/index');
    /*获取指定话题分类下面的文章*/
    Route::get('topicclass/:id/topic/:page','api/:version.TopicClass/topic');
    /*文章详情*/
    Route::get('post/:id','api/:version.Post/index');
    /*获取指定话题下的文章列表*/
    Route::get('topic/:id/post/:page','api/:version.Topic/post')->middleware(['ApiGetUserId']);
    /*获取指定分类下的文章*/
    Route::get('postclass/:id/post/:page','api/:version.PostClass/post')->middleware(['ApiGetUserId']);
    /*获取指定用户下的文章*/
    Route::get('user/:id/post/:page','api/:version.User/post')->middleware(['ApiGetUserId']);
    /*搜索话题*/
    Route::post('search/topic','api/:version.Search/topic')->middleware(['ApiGetUserId']);
    /*搜索文章*/
    Route::post('search/post','api/:version.Search/post')->middleware(['ApiGetUserId']);
    /*搜索用户*/
    Route::post('search/user','api/:version.Search/user');
    /*获取广告数据*/
    Route::get('adsense/:type','api/:version.Adsense/index');
    /*获取文章的所有评论*/
    Route::get('post/:id/comment','api/:version.Post/comment');
    /*检查更新*/
    Route::post('update','api/:version.Update/update');
    /*获取用户统计信息*/
    Route::get('user/getcounts/:user_id','api/:version.User/getCounts');

});

//需要token验证的路由分组
/* 会先经过中间件的判定然后再进行路由*/
Route::group('api/:version/',function (){
    /*退出登录*/
    Route::post('user/logout','api/:version.User/logout');
    /*上传多图*/
    Route::post('image/uploadmore','api/:version.Image/uploadMore');
    /*发布文章*/
    Route::post('post/create','api/:version.Post/create');
    /*获取用户自己的文章*/
    Route::get('user/post/:page','api/:version.User/Allpost');

    /*用户顶踩*/
    Route::post('support','api/:version.Support/index');
    /*发表文章评论*/
    Route::post('post/commont','api/:version.Comment/comment');
    /*修改用户头像*/
    Route::post('editUserpic','api/:version.User/editUserpic');
    /*修改用户信息*/
    Route::post('editUserInfo','api/:version.User/editUserInfo');
    /*修改用户密码*/
    Route::post('rePassword','api/:version.User/rePassword');
    /*拉黑用户*/
    Route::post('addblack','api/:version.Blacklist/addBlack');
    /*取消拉黑用户*/
    Route::post('removeblack','api/:version.Blacklist/removeBlack');
    /*关注用户*/
    Route::post('user/follow','api/:version.User/follow');
    /*取消关注用户*/
    Route::post('user/unfollow','api/:version.User/unfollow');
    /*互关*/
    Route::get('friends/:page','api/:version.User/friends');
    /*获取粉丝列表*/
    Route::get('fens/:page','api/:version.User/fens');
    /*获取关注列表*/
    Route::get('user/follows/:page','api/:version.User/follows');

    /*用户反馈*/
    Route::post('feedback','api/:version.Feedback/feedback');
    /*获取用户反馈列表*/
    Route::get('feedbacklist/:page','api/:version.Feedback/feedbacklist');



})->middleware(['ApiUserAuth','ApiUserBindPhone','ApiUserStatus']);



// socket 部分
Route::group('api/:version/',function(){
    // 发送信息
    Route::post('chat/send','api/:version.Chat/send');
    // 接收未接受信息
    Route::post('chat/get','api/:version.Chat/get');
    // 绑定上线
    Route::post('chat/bind','api/:version.Chat/bind');
})->middleware(['ApiUserAuth','ApiUserBindPhone','ApiUserStatus']);


