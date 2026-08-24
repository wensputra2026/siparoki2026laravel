<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthAndRoleAccessTest extends TestCase
{
    /**
     * Test login page loads successfully.
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /**
     * Test unauthenticated users are redirected from protected panels.
     */
    public function test_unauthenticated_users_are_redirected(): void
    {
        $response = $this->get('/superadmin/user');
        $response->assertRedirect('/login');
    }

    /**
     * Test login with invalid credentials fails.
     */
    public function test_login_with_invalid_credentials_fails(): void
    {
        $response = $this->post('/login', [
            'login' => 'invalid_user@domain.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    /**
     * Test register page can be rendered.
     */
    public function test_register_page_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /**
     * Test forgot password page can be rendered.
     */
    public function test_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get('/lupa-password');
        $response->assertStatus(200);
    }
}
