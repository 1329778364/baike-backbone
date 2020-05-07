<?php

namespace app\common\model;

use app\common\controller\FileController;
use app\lib\exception\BaseException;
use think\Exception;
use think\Model;

class Image extends Model
{
    /*自动写入时间戳*/
    protected $autoWriteTimestamp = true;
    /*1. count函数 不要加self*/

    public function uploadMore()
    {
        $images = $this->upload(request()->userId, 'imglist');
        $imageCount = count($images);
        for ($i = 0; $i < $imageCount; $i++) {
            $images[$i]['url'] = getFileUrl($images[$i]['url']);
        }
        return $images;
    }

    /* 上传文件 */
    public function upload($userid = '', $field = "")
    {
        try {
            $files = request()->file($field);
        } catch (Exception $e) {
            TApiException('请选择图片', 10000, 200);
        }

        if (is_array($files)) {
            $arr = [];
            foreach ($files as $file) {
                $res = FileController::UploadEvent($file);
                if ($res["status"]) {
                    $arr[] = [
                        "url" => $res['data'],
                        "user_id" => $userid
                    ];
                }
            }
            halt($arr);
            return $this->saveAll($arr);
        }

        /*单文件上传*/
        $file = FileController::UploadEvent($files);
        if (!$file['status']) TApiException($file['data'], 10000, 200);

        /*上传成功写入数据库*/
        return self::create([
            'url' => $file['data'],
            'user_id' => $userid
        ]);
    }

    // 图片是否存在
    public function isImageExist($id, $userid)
    {
        return $this->where('user_id', $userid)->field('id')->find($id);
    }
}
