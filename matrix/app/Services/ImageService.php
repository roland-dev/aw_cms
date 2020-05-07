<?php

namespace Matrix\Services;

use Matrix\Contracts\ImageManager;
use Matrix\Models\VideoSignin;
use Matrix\Models\User;
use Matrix\Models\Category;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Matrix\Contracts\CourseVideoManager;

use QrCode;

class ImageService extends BaseService implements ImageManager
{
    const IMAGE_FORMAT_PNG = 'png';

    protected $qrcode;
    protected $videoSignin;
    protected $user;
    protected $category;

    public function __construct( VideoSignin $videoSignin, User $user, Category $category)
    {
        $this->qrcode = QrCode::format(self::IMAGE_FORMAT_PNG)
            ->margin(config('image.qrcode.margin'))
            ->size(config('image.qrcode.size'))
            ->encoding('UTF-8')
            ->errorCorrection('H');

        $this->videoSignin = $videoSignin;
        $this->user = $user;
        $this->category = $category;
    }


    public function getQrCode(string $text, string $logo = '', string $title = '', string $savePath = '')
    {
        if (!empty($logo)) {
            $this->qrcode->mergeString($logo, .3);
        }
        $qrcode = empty($savePath) ? $this->qrcode->generate($text) : $this->qrcode->generate($text, $savePath);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'qrcode' => $qrcode,
            ],
        ];

        return $ret;
    }

    public function qrCodeMerge(string $qrcode, string $category, string $time, string $name, string $content, $description)
    {
         $data = '';
         $image = imagecreatefrompng(resource_path('assets/image/qrcodebg.png'));
         $imgresize = $this->resizeImg($qrcode, config('image.qrcode.resize'), config('image.qrcode.resize'));
         $src = imagecreatefromstring($imgresize);
         imagecopymerge($image, $src, 124, 153, 5, 0, 253, 256, 75 );
         // Copy and merge
         $title = '【'.$category.'】';
         $subject = '【主题】';
         if(!empty($description)){
             $view = '【主要观点】';
             $cutlength = 26;
             $res = '';
             $strToArr = explode("\n", $description);
             foreach ($strToArr as $subStr) {
                 $sublen = mb_strlen($subStr);
                 $res .= $this->formatString($sublen, $subStr, $cutlength);
             }
         }

         imagecopymerge($image, $src, 124, 153, 5, 0, 253, 256, 75 );
         imagealphablending($image, true);
         
         $red    = imagecolorallocate($image, 255, 0, 0);

         $fontPath = resource_path('assets/font/simsun.ttc');
         imagefttext($image, 13, 0, 25, 530, $red, $fontPath , $title);
         if(strlen($title) < 5){
             imagefttext($image, 13, 0, 155, 530, $red, $fontPath, $time);
             imagefttext($image, 13, 0, 250, 530, $red, $fontPath, $name);
         }else{
             imagefttext($image, 13, 0, 240, 530, $red, $fontPath, $time);
             imagefttext($image, 13, 0, 325, 530, $red, $fontPath, $name);
         }
         imagefttext($image, 13, 0, 25, 580, $red,  $fontPath, $subject);
         imagefttext($image, 13, 0, 115, 580, $red, $fontPath, $content);
         if(!empty($description)){
             imagefttext($image, 13, 0, 25, 630, $red,  $fontPath, $view);
	     imagefttext($image, 13, 0, 25, 655, $red,  $fontPath, $res);
         }
        
         ob_start();
         //header('Content-type: image/png');
         ImagePng($image);
         $res = ob_get_clean();
         $imgInfo = getimagesizefromstring($res);
         $type = array_get($imgInfo, 'mime');
         return $type === "image/png" ? base64_encode($res) : [];//need to do
    }

    public function resizeImg($imgsrc, $imgwidth, $imgheight)
    {
         $arr = getimagesizefromstring($imgsrc);
         $imgWidth = $imgwidth;
	     $imgHeight = $imgheight;
         $src = imagecreatefromstring($imgsrc);
	     $image = imagecreatetruecolor($imgWidth, $imgHeight);
	     imagecopyresampled($image, $src, 0, 0, 0, 0,$imgWidth,$imgHeight,$arr[0], $arr[1]);
         ob_start();//将图片转换声字节流
         ImagePng($image);
         $res = ob_get_clean();
         return $res;
    }



    public function formatString($sublen, $sourcestr, $cutlength)
    {
        $count = ceil($sublen/$cutlength);
        $startPos = 0;
        $res = '';
        for($i = 0; $i < $count; $i++)
        {
            $resStr = mb_substr($sourcestr, $startPos, $cutlength);
            $res .= "$resStr\n";

            $startPos = $cutlength * ($i + 1);
        }

        return $res;
    }



    public function getUserInfo($authorId)
    {
        $userInfo = $this->user->getUserInfo($authorId);         
        $ret = [
           'code' => SYS_STATUS_OK,
           'userInfo' => $userInfo,  
        ];
     
        return $ret;
    }


    public function upload($file, string $dir)
    {
        $path = storage_path('app/public/');
        $date = date('Y-m-d');
        $dirPath = $path.$dir.'/'.$date;
        $dir = $dir.'/'.$date;
        if(!is_dir($dirPath)){
            Storage::disk('public')->makeDirectory($dir);
        }
        $filePath = Storage::disk('public')->putFile($dir, $file, 'public');
        $fileSize = Storage::disk('public')->size($filePath);
        $fileOriginalName = $file->getClientOriginalName();
        $fileOriginalExtension = $file->getClientOriginalExtension();
        $fileInfo = [
            'file_path' => config('app.url').'/storage/'.$filePath,
            'relatively_file_path' => $filePath,
            'cnd_relatively_file_path' => config('cdn.cdn_uri').$filePath,
            'file_size' => $fileSize,
            'file_original_name' => $fileOriginalName,
            'file_original_extension' => $fileOriginalExtension,
        ];
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $fileInfo,
        ];
 
        return $ret;
    }

    public function fileDelete($filePath)
    {
        if(is_array($filePath)){
            foreach ($filePath as $path) {
                $exists = Storage::disk('public')->exists($path);
                if(!empty($exists)){
                    Storage::disk('public')->delete($path);
                }
            }
        }else{
            $exists = Storage::disk('public')->exists($filePath);
            if(!empty($exists)){
                Storage::disk('public')->delete($filePath);
            }
        }
    }

    public function makeThumbnail($file, $thumbnailDir)
    {
        $path = storage_path('app/public/');
        $date = date('Y-m-d');
        $thumbnailDirPath = $path.$thumbnailDir.'/'.$date;
        $thumbnailDir = $thumbnailDir.'/'.$date;
        if(!is_dir($thumbnailDirPath)){
            Storage::disk('public')->makeDirectory($thumbnailDir);
        }
        $img = Image::make($file);
        $fileOriginalExtension = $file->getClientOriginalExtension();
        $fileOriginalName = $file->getClientOriginalName();
        $thumbnailName = uniqid().'.'.$file->getClientOriginalExtension();
        $thumbnailFilePath = $thumbnailDirPath.'/'.$thumbnailName;
        $resizeImg = $img->resize(null, 200, function ($constraint){
            $constraint->aspectRatio();
        })->save($thumbnailFilePath);
        $size = $resizeImg->filesize();
        $fileInfo = [
            'file_path' => config('app.url').'/storage/'.$thumbnailDir.'/'.$thumbnailName,
            'relatively_file_path' => $thumbnailDir.'/'.$thumbnailName,
            'cdn_relatively_file_path' => config('cdn.cdn_uri').$thumbnailDir.'/'.$thumbnailName,
            'original_name' => $fileOriginalName,
            'original_extension' => $fileOriginalExtension,
            'size' => $size,
        ];
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $fileInfo,
        ];
 
        return $ret;
    }

    public function thumbnailFileDelete($thumbnailImagePath)
    {
        if(is_array($thumbnailImagePath)){
            foreach ($thumbnailImagePath as $path) {
                $exists = Storage::disk('public')->exists($path);
                if(!empty($exists)){
                    Storage::disk('public')->delete($path);
                }
            }
        }else{
            $exists = Storage::disk('public')->exists($thumbnailImagePath);
            if(!empty($exists)){
                Storage::disk('public')->delete($thumbnailImagePath);
            }
        }

    }
}
