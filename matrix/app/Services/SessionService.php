<?php

namespace Matrix\Services;

use Illuminate\Http\Request;
use Matrix\Contracts\SessionManager;

class SessionService extends BaseService implements SessionManager
{

    public function getSession(Request $request)
    {
    }

    public function setSession(Request $request)
    {
    }
}
