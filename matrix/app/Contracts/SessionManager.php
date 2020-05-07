<?php

namespace Matrix\Contracts;

use Illuminate\Http\Request;

interface SessionManager extends Baseinterface
{
    public function getSession(Request $request);
    public function setSession(Request $request);
}
