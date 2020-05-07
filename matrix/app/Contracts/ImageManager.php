<?php

namespace Matrix\Contracts;

interface ImageManager extends BaseInterface
{
    public function getQrCode(string $text, string $logo, string $title, string $savePath);
    public function qrCodeMerge(string $qrcode, string $category, string $time, string $name, string $content,  $description);
    public function upload($file, string $dir);
    public function fileDelete($filePath);
    public function makeThumbnail($file, $thumbnailDir);
    public function thumbnailFileDelete($thumbnailImagePath);
}

