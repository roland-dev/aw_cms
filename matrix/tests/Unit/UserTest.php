<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

use Matrix\Contracts\UserManager;

class UserTest extends TestCase
{
    public function testLogout()
    {
        $userManager = app(UserManager::class);
        $logoutResult = $userManager->logout();
        $assert = (SYS_STATUS_OK === $logoutResult['code']) && !Auth::check();

        $this->assertTrue($assert);
    }
}
