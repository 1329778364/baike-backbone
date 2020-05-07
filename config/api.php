<?php

return [
//    token过期时间
    "token_expire"=>0,
    'aliSMS'=>[
        'isopen'=> false,//开启阿里大于
        'accessKeyId'=>'<accessKeyId>',
        'accessSecret'=>'<accessSecret>',
        'regionId'=>'cn-hangzhou',
        'product'=>'Dysmsapi',
        'version'=>'2017-05-25',
        'SignName'=>'<YourSignName>',
        'TemplateCode'=>'<YourTemplateCode>',
        'expire'=>60 // 验证码发送时间间隔（60秒）
    ]
];