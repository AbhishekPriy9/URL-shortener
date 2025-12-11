<?php

namespace Tests\Feature;

use App\Mail\SendInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => bcrypt('password'),
            'role' => 'SuperAdmin',
        ]);
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('super.dashboard.index');
    }

    public function test_non_super_admin_cannot_access_super_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $response = $this->actingAs($admin)->get('/super/dashboard');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Unauthorized access');
    }

    public function test_super_admin_can_view_client_creation_form(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super/clients/create');

        $response->assertStatus(200);
        $response->assertViewIs('super.clients.create');
    }

    public function test_super_admin_can_invite_admin(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->superAdmin)->post('/super/clients/store', [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
        ]);

        $response->assertRedirect(route('super.dashboard.index'));
        $response->assertSessionHas('success', 'Invitation sent successfully');

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'role' => 'Admin',
            'name' => 'New Admin',
        ]);

        $newAdmin = User::where('email', 'newadmin@example.com')->first();
        $this->assertEquals('Admin', $newAdmin->role);

        Mail::assertSent(SendInvitation::class, function ($mail) use ($newAdmin) {
            return $mail->hasTo('newadmin@example.com');
        });
    }

    public function test_super_admin_cannot_create_short_urls(): void
    {
        // Super admin should not have access to URL creation routes
        $response = $this->actingAs($this->superAdmin)->get('/super/urls/create');

        $response->assertStatus(404);
    }

    public function test_super_admin_dashboard_shows_all_admins(): void
    {
        // Create some admins
        User::create([
            'name' => 'Admin 1',
            'email' => 'admin1@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        User::create([
            'name' => 'Admin 2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $response = $this->actingAs($this->superAdmin)->get('/super/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('admins');
    }

    public function test_super_admin_invite_validates_required_fields(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super/clients/store', []);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_super_admin_invite_validates_email_format(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super/clients/store', [
            'name' => 'Test Admin',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_super_admin_invite_validates_unique_email(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $response = $this->actingAs($this->superAdmin)->post('/super/clients/store', [
            'name' => 'New User',
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
