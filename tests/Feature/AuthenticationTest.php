<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('member.dashboard.index'));
    }

    public function test_user_receives_error_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Invalid credentials');
    }

    public function test_superadmin_redirected_to_super_dashboard_after_login(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => bcrypt('password'),
            'role' => 'SuperAdmin',
        ]);

        $response = $this->post('/login', [
            'email' => 'super@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($superAdmin);
        $response->assertRedirect(route('super.dashboard.index'));
    }

    public function test_admin_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard.index'));
    }

    public function test_member_redirected_to_member_dashboard_after_login(): void
    {
        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        $response = $this->post('/login', [
            'email' => 'member@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($member);
        $response->assertRedirect(route('member.dashboard.index'));
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    public function test_unauthenticated_users_cannot_access_protected_routes(): void
    {
        $response = $this->get('/super/dashboard');
        $response->assertRedirect(route('login'));

        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));

        $response = $this->get('/member/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_login_validates_email_field(): void
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }
}
