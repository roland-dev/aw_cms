<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\MoveQrContract;
use Exception;
use Cache;

class OperateController extends Controller
{
    //
    const MOVEQR_FILE_CACHE_FORMAT = 'moveqr_file_cache_default_%s';
    protected $request;
    protected $moveQr;

    public function __construct(Request $request, MoveQrContract $moveQr)
    {
        $this->request = $request;
        $this->moveQr = $moveQr;
    }

    public function cacheClear(string $groupCode)
    {
        try {
            $moveQrGroup = $this->moveQr->getMoveQrGroup($groupCode);
            $moveQrList = array_get($moveQrGroup, 'move_qr_list');
            if (!empty($moveQrList)) {
                $moveQrList->each(function ($item, $key) {
                    $cacheKey = sprintf(self::MOVEQR_FILE_CACHE_FORMAT, $item->code);
                    Cache::forget($cacheKey);
                });
            }
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '活码计数缓存清除成功',
            ];
        } catch (Exception $e) {
            Log::error('Move QR Cache clear failed!', [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '活码计数缓存清除失败',
            ];
        }

        return $ret;
    }

    public function uploadImage()
    {
        $credentials = $this->request->validate([
            'image' => 'required|image|mimes:png',
        ]);

        $path = $this->request->image->store('public/moveqr');
        $pathArr = explode('/', $path);
        $filename = $pathArr[2];
        $ret = count($pathArr) == 3 ? [
            'code' => SYS_STATUS_OK,
            'msg' => '上传图片成功',
            'data' => [
                'filename' => $filename,
                'url' => sprintf('%s%s', config('promotion.moveqr.base_url'), $filename),
            ],
        ] : [
            'code' => SYS_STATUS_ERROR_UNKNOW,
            'msg' => '图片路径配置错误',
        ];

        return $ret;
    }

    public function createMoveQrGroup()
    {
        $credentials = $this->request->validate([
            'title' => 'required|string',
            'max_fans' => 'required|integer',
        ]);

        $title = array_get($credentials, 'title');
        $maxFans = array_get($credentials, 'max_fans');

        $moveQrGroup = $this->moveQr->createQrGroup($title, $maxFans);

        $ret = empty($moveQrGroup) ? [
            'code' => SYS_STATUS_ERROR_UNKNOW,
            'msg' => '活码创建失败',
        ] : [
            'code' => SYS_STATUS_OK,
            'msg' => '活码创建成功',
            'data' => [
                'move_qr_group' => $moveQrGroup,
            ],
        ];

        return $ret;
    }

    public function updateMoveQrGroup(string $groupCode)
    {
        $credentials = $this->request->validate([
            'title' => 'required|string',
            'max_fans' => 'required|integer',
        ]);

        $title = array_get($credentials, 'title');
        $maxFans = array_get($credentials, 'max_fans');

        $moveQrGroup = $this->moveQr->updateQrGroup($groupCode, $title, $maxFans);

        $ret = empty($moveQrGroup) ? [
            'code' => SYS_STATUS_ERROR_UNKNOW,
            'msg' => '活码修改失败',
        ] : [
            'code' => SYS_STATUS_OK,
            'msg' => '活码修改成功',
            'data' => [
                'move_qr_group' => $moveQrGroup,
            ],
        ];

        return $ret;
    }

    public function removeMoveQrGroup(string $groupCode)
    {
        $code = $this->moveQr->removeQrGroup($groupCode);

        $ret = [
            'code' => $code,
            'msg' => empty($code) ? '删除成功' : '删除失败',
        ];

        return $ret;
    }

    public function getMoveQrGroupList()
    {
        $moveQrGroupList = $this->moveQr->getMoveQrGroupList();

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'move_qr_group_list' => $moveQrGroupList,
            ],
        ];

        return $ret;
    }

    public function updateMoveQr(string $qrCode)
    {
        $moveQrData = $this->request->validate([
            'title' => 'required|string',
            'sort' => 'required|integer',
        ]);

        $title = array_get($moveQrData, 'title');
        $sort = array_get($moveQrData, 'sort');


        $moveQr = $this->moveQr->updateMoveQr($qrCode, $title, $sort);

        $ret = empty($moveQr) ? [
            'code' => SYS_STATUS_ERROR_UNKNOW,
            'msg' => '活码更新失败',
        ] : [
            'code' => SYS_STATUS_OK,
            'msg' => '活码更新成功',
            'data' => [
                'move_qr' => $moveQr,
            ],
        ];

        return $ret;
    }

    public function createMoveQr()
    {
        $moveQrData = $this->request->validate([
            'move_qr_group_code' => 'required|exists:move_qr_groups,code',
            'title' => 'required|string',
            'filename' => 'required|string',
            'sort' => 'required|integer',
        ]);

        $moveQrGroupCode = array_get($moveQrData, 'move_qr_group_code');
        $title = array_get($moveQrData, 'title');
        $filename = array_get($moveQrData, 'filename');
        $sort = array_get($moveQrData, 'sort');

        $moveQr = $this->moveQr->createMoveQr($moveQrGroupCode, $title, $filename, $sort);
        $ret = empty($moveQr) ? [
            'code' => SYS_STATUS_ERROR_UNKNOW,
            'msg' => '活码创建失败',
        ] : [
            'code' => SYS_STATUS_OK,
            'msg' => '活码创建成功',
            'data' => [
                'move_qr' => $moveQr,
            ],
        ];

        return $ret;
    }

    public function removeMoveQr(string $qrCode)
    {
        $code = $this->moveQr->removeQr($qrCode);

        $ret = [
            'code' => $code,
            'msg' => empty($code) ? '删除成功' : '删除失败',
        ];

        return $ret;
    }
}
