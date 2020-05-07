<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\ImageManager;

class ImgController extends Controller
{
    private $request;
    private $imageManager;

    public function __construct(Request $request, ImageManager $imageManager)
    {
        $this->request = $request; 
        $this->imageManager = $imageManager;
    }

    public function generate()
    {
          $savePath = '';
          $text = config('video.video.url').'test01';
          $path = 'http://d.hiphotos.baidu.com/image/pic/item/d833c895d143ad4b3ae286d88e025aafa50f06de.jpg';
         //$path = "http://p.qlogo.cn/bizmail/cKDowEpjTiarn59sXInXzic68UeaMtQdX7FkZsJ5gskibKpNlu6RTeBUA/0";
          $data = file_get_contents($path);
          $title = 'test';
          //$savePath = '/home/sevenjking/site/sf_sevenjking/Desktop/qrcodeimg/qrcode1.png';

          $qrCodeData = $this->imageManager->getQrCode($text, $data, $title, $savePath);
          $qrcode = array_get($qrcode, 'qrcode');
          
          $qrCode = $this->imageManager->qrCodeMerge();
 
		  //$qrCode = $this->imageManager->test($text, $log, $title, $savePath);        
 
          dd($qrCode);
    }

}
