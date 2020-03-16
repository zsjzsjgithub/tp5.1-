<?php
/**
 * Created by shihe
 * User: Administrator
 * Date: 2019/4/10
 * Time: 14:07
 */

namespace comservice;


use Endroid\QrCode\QrCode;
use think\Image;

class Upload
{
    //单图
    public static function uploadOne($file, $filepath, $filesave)
    {
        if (!file_exists($filepath)) {
            mkdir($filepath, 0700, true);
        }
           $info=$file->move($filepath);
        // $info = $files->move($filepath);
        if (!$info) {
            return ['errcode' => 1, 'msg' => $file->getError()];
        }
        $imgPath = $filesave . $info->getSaveName();
        $imgPaths=str_replace('\\','/',$imgPath);
        return ['errcode' => 0, 'msg' => $imgPaths];
    }

    //多图上传,带缩略图
    public static function uploads($files, $filepath, $filesave)
    {
        if (!file_exists($filepath)) {
            mkdir($filepath, 0700, true);
        }
        //$file->validate(['size'=>15678,'ext'=>'jpg,png,gif']);
        $image = Image::open($files);
        $fileType = $image->type();
        $fileName = time() . uniqid() . '.' . $fileType;
        $res = $image->thumb(100, 100)->save($filepath . $fileName);
        // $info = $files->move($filepath);
        if (!$res) {
            return ['errcode' => 1, 'msg' => $files->getError()];
        }
        $imgPath = $filesave . $fileName;
        return ['errcode' => 0, 'msg' => $imgPath];
    }
    //base64图片上传
    public static function uploadBase64($rootPath,$base64)
    {
        //$base64_image_content = $base64;
        //匹配出图片的格式
        $new_file = $rootPath;
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }
        $new_file = $new_file.time().mt_rand(1000000,9999999) . ".jpg";
        if (file_put_contents($new_file, base64_decode($base64))) {
            $img_path = str_replace($rootPath, '', $new_file);
            return ['errcode'=>0,'msg'=>'新文件保存成功','result'=>$img_path];

        } else {
            return ['errcode'=>1,'msg'=>'新文件保存失败'];
        }
    }
    //生成二维码
    public static function qrcode($content,$qrcodeSize=250,$qrcodeType='png')
    {
        $qrCode = new  QrCode($content);
        $qrCode->setSize($qrcodeSize);
        $qrCode->setWriterByName($qrcodeType);
        $qrCode->setMargin(10);
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 1]);
       // $qrCode->setBackgroundColor(['r' => 213, 'g' => 241, 'b' => 251, 'a' => 0]);
       // $qrCode->setLogoPath(__DIR__.'/../assets/images/symfony.png');
        $qrCode->setLogoSize(70);
        //保存二维码
        $qrcodePath = '../imgs/';
        if (!file_exists($qrcodePath)) {
            mkdir($qrcodePath, 0700, true);
        }
        $imgName =  time() .uniqid(). '.' . 'png';
        $qrCode->writeFile($qrcodePath . $imgName);
        return $imgName;
        $image = Image::open('../imgs/haibao.png');
// 给原图左上角添加透明度为50的水印并保存alpha_image.png
        $alpha=time() .uniqid(). '.' . 'png';
        $image->water('../imgs/'.$imgName,Image::WATER_CENTER,100)->save('../imgs/'.$alpha);
        return $alpha;
    }
}