<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    //
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function createImage()
    {
        if (!$this->request->hasFile('image')) {
            abort(400);
        }
        $path = $this->request->image->store('public');
        $type = $this->request->input('type');
        if ($type == 'wangeditor') {
            $ret = [
                'errno' => SYS_STATUS_OK,
                'data' => [
                    config('app.cdn.base_url').Storage::url($path),
                ],
            ];
        } else {
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'path' => config('app.cdn.base_url').Storage::url($path),
                ],
            ];
        }

        return $ret;
    }
}
