<?php

namespace Matrix\Services;

use Matrix\Contracts\MoveQrContract;
use Matrix\Models\MoveQrGroup;
use Matrix\Models\MoveQr;
use Exception;
use Cache;
use Log;
use DB;

class MoveQrService extends BaseService implements MoveQrContract
{
    const CODE_LENGTH = 16;
    const MOVEQR_FILE_CACHE_FORMAT = 'moveqr_file_cache_default_%s';

    public function createQrGroup(string $title, int $maxFans = 0, string $remark = '')
    {
        try {
            $qrGroup = MoveQrGroup::create([
                'code' => str_random(self::CODE_LENGTH),
                'title' => $title,
                'max_fans' => $maxFans,
                'remark' => $remark,
            ]);
        } catch (Exception $e) {
            Log::error('Create QR group failed!', [$e]);
            $qrGroup = NULL;
        }

        return $qrGroup;
    }

    public function updateQrGroup(string $groupCode, string $title, int $maxFans, string $remark = '')
    {
        try {
            $qrGroup = MoveQrGroup::where('code', $groupCode)->firstOrFail();
            $qrGroup->title = $title;
            $qrGroup->max_fans = $maxFans;
            $qrGroup->remark = $remark;
            $qrGroup->save();
        } catch (Exception $e) {
            Log::error('Update QR group failed!', [$e]);
            $qrGroup = NULL;
        }

        return $qrGroup;
    }

    public function removeQrGroup(string $groupCode)
    {
        DB::beginTransaction();
        try {
            $qrGroup = MoveQrGroup::where('code', $groupCode)->firstOrFail();
            $qrList = MoveQr::where('move_qr_group_code', $groupCode)->get();
            $qrList->each(function ($item, $key) {
                $item->delete();
            });
            $qrGroup->delete();

            DB::commit();

            return SYS_STATUS_OK;
        } catch (Exception $e) {
            Log::error('Remove QR group failed!', [$e]);
            DB::rollBack();
            return SYS_STATUS_ERROR_UNKNOW;
        }
    }

    public function getMoveQrGroup(string $groupCode)
    {
        try {
            $qrGroup = MoveQrGroup::where('code', $groupCode)->firstOrFail()->toArray();
            $qrList = MoveQr::where('move_qr_group_code', $groupCode)->orderBy('sort', 'desc')->get();
            $qrGroup['move_qr_list'] = $qrList;

        } catch (Exception $e) {
            Log::error('Get QR group failed!', [$e]);
            $qrGroup = NULL;
        }

        return $qrGroup;
    }

    public function getMoveQrGroupList()
    {
        $qrGroupList = MoveQrGroup::orderBy('created_at', 'asc')->get()->toArray();
        $qrGroupList = array_column($qrGroupList, NULL, 'code');
        $qrList = MoveQr::orderBy('sort', 'desc')->get();

        $qrList->each(function ($item, $key) use (&$qrGroupList) {
            if (!array_key_exists($item->move_qr_group_code, $qrGroupList)) {
                return ;
            }
            if (!array_key_exists('move_qr_list', $qrGroupList[$item->move_qr_group_code])) {
                $qrGroupList[$item->move_qr_group_code]['move_qr_list'] = [];
            }
            $itemArr = $item->toArray();
            $itemArr['url'] = sprintf('%s%s', config('promotion.moveqr.base_url'), $item->filename);
            $cacheKey = sprintf(self::MOVEQR_FILE_CACHE_FORMAT, $item->code);
            $itemArr['show_cnt'] = (int)Cache::get($cacheKey);
            $qrGroupList[$item->move_qr_group_code]['move_qr_list'][] = $itemArr;
        });

        return array_values($qrGroupList);
    }

    public function createMoveQr(string $groupCode, string $title, string $filename, int $sort, string $remark = '')
    {
        try {
            $moveQr = MoveQr::create([
                'code' => str_random(self::CODE_LENGTH),
                'move_qr_group_code' => $groupCode,
                'title' => $title,
                'filename' => $filename,
                'sort' => $sort,
                'remark' => $remark,
            ]);
        } catch (Exception $e) {
            Log::error('Create move QR failed!', [$e]);
            $moveQr = NULL;
        }

        return $moveQr;
    }

    public function updateMoveQr(string $code, string $title, int $sort, string $remark = '')
    {
        try {
            $moveQr = MoveQr::where('code', $code)->firstOrFail();
            $moveQr->title = $title;
            $moveQr->sort = $sort;
            $moveQr->remark = $remark;
            $moveQr->save();
        } catch (Exception $e) {
            Log::error('Update move QR failed!', [$e]);
            $moveQr = NULL;
        }

        return $moveQr;
    }

    public function removeQr(string $code)
    {
        try {
            $moveQr = MoveQr::where('code', $code)->firstOrFail();
            $moveQr->delete();

            return SYS_STATUS_OK;
        } catch (Exception $e) {
            Log::error('Remove move QR failed!', [$e]);
            return SYS_STATUS_ERROR_UNKNOW;
        }
    }
}
