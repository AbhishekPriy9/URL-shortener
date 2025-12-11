<?php

namespace Tests\Feature;

use App\Mail\SendInvitation;
use App\Models\Shortener;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
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

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard.index');
    }

    public function test_admin_cannot_access_super_admin_routes(): void
    {
        $response = $this->actingAs($this->admin)->get('/super/dashboard');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Unauthorized access');
    }

    public function test_admin_cannot_access_member_routes(): void
    {
        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'invited_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/member/dashboard');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Unauthorized access');
    }

    public function test_admin_can_view_client_creation_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/clients/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.clients.create');
    }

    public function test_admin_can_invite_admin(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post('/admin/clients/store', [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'role' => 'Admin',
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));
        $response->assertSessionHas('success', 'Invitation sent successfully');

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'role' => 'Admin',
            'invited_by' => $this->admin->id,
        ]);

        Mail::assertSent(SendInvitation::class);
    }

    public function test_admin_can_invite_member(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post('/admin/clients/store', [
            'name' => 'New Member',
            'email' => 'newmember@example.com',
            'role' => 'Member',
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));
        $response->assertSessionHas('success', 'Invitation sent successfully');

        $this->assertDatabaseHas('users', [
            'email' => 'newmember@example.com',
            'role' => 'Member',
            'invited_by' => $this->admin->id,
        ]);

        Mail::assertSent(SendInvitation::class);
    }

    public function test_admin_can_view_url_creation_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/urls/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.urls.create');
    }

    public function test_admin_can_create_short_url(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'https://www.example.com/very/long/url/path',
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));
        $response->assertSessionHas('success', 'Short URL generated successfully.');

        $this->assertDatabaseHas('shorteners', [
            'long_url' => 'https://www.example.com/very/long/url/path',
            'user_id' => $this->admin->id,
        ]);

        $shortener = Shortener::where('user_id', $this->admin->id)->first();
        $this->assertNotNull($shortener);
        $this->assertEquals(8, strlen($shortener->short_url));
    }

    public function test_admin_dashboard_shows_own_urls(): void
    {
        Shortener::create([
            'long_url' => 'https://example.com/test1',
            'short_url' => 'abc12345',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('shortURLs');
    }

    public function test_admin_dashboard_shows_member_urls(): void
    {
        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'invited_by' => $this->admin->id,
        ]);

        Shortener::create([
            'long_url' => 'https://example.com/member-url',
            'short_url' => 'xyz98765',
            'user_id' => $member->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('shortURLs');
        $response->assertViewHas('members');
    }

    public function test_admin_invite_requires_role_field(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/clients/store', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['role']);
    }

    public function test_admin_invite_validates_role_values(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/clients/store', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'SuperAdmin', // Not allowed
        ]);

        $response->assertSessionHasErrors(['role']);
    }

    public function test_admin_invite_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/clients/store', []);

        $response->assertSessionHasErrors(['name', 'email', 'role']);
    }

    public function test_admin_url_creation_validates_url_format(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'not-a-valid-url',
        ]);

        $response->assertSessionHasErrors(['long_url']);
    }

    public function test_admin_url_creation_requires_url_field(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/urls/store', []);

        $response->assertSessionHasErrors(['long_url']);
    }
}
