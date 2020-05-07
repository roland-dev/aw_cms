<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\TalkshowContract;
use Matrix\Contracts\OperateLogContract;

use Matrix\Exceptions\MatrixException;
use Exception;
use Auth;
use Log;

class VideoVendorController extends Controller
{
    //
    private $request;
    private $talkshow;
    private $operateLog;

    public function __construct(Request $request, TalkshowContract $talkshow, OperateLogContract $operateLog)
    {
        $this->request = $request;
        $this->talkshow = $talkshow;
        $this->operateLog = $operateLog;
    }

    public function getVideoVendorList()
    {
        $credentials = $this->request->validate([
            'page_no' => 'required|integer',
            'page_size' => 'required|integer',
            'vendor_code' => 'string|nullable',
            'vendor_name' => 'string|nullable',
        ]);

        try {
            $pageNo = array_get($credentials, 'page_no');
            $pageSize = array_get($credentials, 'page_size');

            $videoVendorList = $this->talkshow->getVideoVendorList($pageNo, $pageSize, [
                'code' => array_get($credentials, 'vendor_code'),
                'name' => array_get($credentials, 'vendor_name'),
            ]);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'video_vendor_list' => $videoVendorList,
                ],
            ];
        } catch(Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function createVideoVendor(string $vendorCode)
    {
        $credentials = $this->request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'domain' => 'required|string',
            'remark' => 'string',
        ]);

        $credentials['last_modify_user_id'] = Auth::user()->id;

        try {
            $vendor = $this->talkshow->createVideoVendor($credentials);
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'video_vendor' => $vendor,
                ],
            ];
            $this->operateLog->record('create', 'video_vendor', $vendor->id, "用户 ".Auth::user()->name." 创建了一个供应商 {$vendor}", $this->request->ip(), Auth::user()->id);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '创建失败，发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function updateVideoVendor(string $vendorCode)
    {
        $credentials = $this->request->validate([
            'code' => 'string',
            'name' => 'string',
            'domain' => 'string',
            'remark' => 'string',
        ]);

        try {
            if (empty($credentials)) {
                throw new MatrixException('什么都不传您打算让我更新什么呢？', SYS_STATUS_OK);
            }

            $credentials['last_modify_user_id'] = Auth::user()->id;
            $vendor = $this->talkshow->updateVideoVendor($vendorCode, $credentials);
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'video_vendor' => $vendor,
                ],
            ];
            $this->operateLog->record('update', 'video_vendor', $vendor->id, "用户 ".Auth::user()->name." 更新了一个供应商 {$vendor}", $this->request->ip(), Auth::user()->id);
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function removeVideoVendor(string $vendorCode)
    {
        try {
            $this->talkshow->removeVideoVendor($vendorCode);
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
            ];
            $this->operateLog->record('delete', 'video_vendor', $vendorCode, "用户 ".Auth::user()->name." 删除了一个供应商 $vendorCode", $this->request->ip(), Auth::user()->id);
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function getVideoVendor(string $vendorCode)
    {
        try {
            $vendor = $this->talkshow->getVideoVendor($vendorCode);
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'video_vendor' => $vendor,
                ],
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

}
