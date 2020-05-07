<?php

namespace app\common\controller;

use think\Controller;
use think\Request;
use app\common\controller\BaseController;


class FileController extends BaseController
{

    public function index()
    {
    }


    public static function UploadEvent($file, $size = '2067800', $ext = 'jpg,png,gif', $path = "uploads")
    {
        $info = $file->validate(['size' => $size, "ext" => $ext])->move($path);
        return [
            "data" => $info ? $info->getPathname() : $file->getError(),
            "status" => $info ? true : false
        ];
    }
}
