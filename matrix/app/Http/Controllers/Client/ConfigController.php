<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;

class configController extends Controller
{
  private $request;

  public function __construct(
    Request $request
  )
  {
    $this->request = $request;
  }

  public function getVhallParams () {
    $appKey = '';
    $secretKey = '';

    $strUa = $this->request->userAgent() ? strtolower($this->request->userAgent()) : '';
    if ($strUa && preg_match('#zytg#', $strUa)) {
      $appKey = config('token.vhall.app_key');
      $secretKey = config('token.vhall.app_secret_key');
    }

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'param_a' => $appKey,
        'param_b' => $secretKey
      ],
    ];

    return $ret;
  }
}