<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\MoveQrContract;
use Cache;

class PromotionController extends Controller
{
    //
    const MOVEQR_FILE_CACHE_FORMAT = 'moveqr_file_cache_default_%s';
    const MOVEQR_FILE_COMMISSION_CACHE_FORMAT = 'moveqr_file_cache_%s_%s';

    protected $moveQr;

    public function __construct(MoveQrContract $moveQr)
    {
        $this->moveQr = $moveQr;
    }

    public function moveQrNew(string $qrGroupCode, string $report = 'default')
    {
        $baseUrl = config('promotion.moveqr.base_url');
        $moveQrGroup = $this->moveQr->getMoveQrGroup($qrGroupCode);
        $maxFans = array_get($moveQrGroup, 'max_fans');
        $qrList = array_get($moveQrGroup, 'move_qr_list');

        $viewQr = $qrList->filter(function ($item, $key) use ($maxFans) {
            $cacheKey = sprintf(self::MOVEQR_FILE_CACHE_FORMAT, $item->code);
            $viewCount = (int)Cache::get($cacheKey);
            return $viewCount < $maxFans;
        })->first();

        if (empty($viewQr)) {
            $viewQr = $qrList->random();
        }
        $cacheKey = sprintf(self::MOVEQR_FILE_CACHE_FORMAT, $viewQr->code);
        $res = Cache::increment($cacheKey);

        $ret = [
            'view_qr_file_url' => sprintf('%s%s', $baseUrl, $viewQr->filename),
            'report' => $report,
        ];

        return view('promotion.moveqr', $ret);
    }

    public function moveQr(string $report = 'default')
    {
        $maxFans = config('promotion.moveqr.max_fans');
        $baseUrl = config('promotion.moveqr.base_url');
        $fileList = config('promotion.moveqr.file_list');
        $fileList = empty($fileList) ? [] : explode(',', $fileList);

        $viewQrFile = '';
        foreach ($fileList as $key => $file) {
            $cacheKey = sprintf(self::MOVEQR_FILE_CACHE_FORMAT, $file);
            $viewCount = (int)Cache::get($cacheKey);

            if (count($fileList) - 1 == $key && $viewCount >= $maxFans) {
                $fileIndex = rand(0, count($fileList) - 1);
                $viewQrFile = $fileList[$fileIndex];
                break;
            }

            if ($viewCount >= $maxFans) {
                continue;
            }

            $viewQrFile = $file;
            Cache::increment($cacheKey);
            break;
        }

        $ret = [
            'view_qr_file_url' => "$baseUrl$viewQrFile",
            'report' => "$report",
        ];

        return view('promotion.moveqr', $ret);
    }

    public function commissionMoveQr(Request $request, string $commissionCode, string $report = 'default')
    {
        $uri = $request->path();
        return $uri;
        $baseUrl = config('promotion.moveqr.base_url');
        $maxFans =  config("promotion.moveqr.$commissionCode.max_fans");
        $fileList = config("promotion.moveqr.$commissionCode.file_list");
        $fileList = empty($fileList) ? [] : explode(',', $fileList);

        $viewQrFile = '';
        foreach ($fileList as $key => $file) {
            $cacheKey = sprintf(self::MOVEQR_FILE_COMMISSION_CACHE_FORMAT, $commissionCode, $file);
            $viewCount = (int)Cache::get($cacheKey);

            if (count($fileList) - 1 == $key && $viewCount >= $maxFans) {
                $fileIndex = rand(0, count($fileList) - 1);
                $viewQrFile = $fileList[$fileIndex];
                break;
            }

            if ($viewCount >= $maxFans) {
                continue;
            }

            $viewQrFile = $file;
            Cache::increment($cacheKey);
            break;
        }

        $ret = [
            'view_qr_file_url' => "$baseUrl$viewQrFile",
        ];

        return view('promotion.commission_moveqr', $ret);
    }
}
