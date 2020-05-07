<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class UserTest extends TestCase
{
    public function testLogout()
    {
        $response = $this->get('/user/logout');

        if (Auth::check()) {
            $this->assertTrue(false);
        }

        $response->assertStatus(200);
    }
}
