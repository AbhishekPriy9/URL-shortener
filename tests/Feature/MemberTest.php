<?php

namespace Tests\Feature;

use App\Models\Shortener;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    protected User $member;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $this->member = User::create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'invited_by' => $this->admin->id,
        ]);
    }

    public function test_member_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->member)->get('/member/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('member.dashboard.index');
    }

    public function test_member_cannot_access_super_admin_routes(): void
    {
        $response = $this->actingAs($this->member)->get('/super/dashboard');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Unauthorized access');
    }

    public function test_member_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->member)->get('/admin/dashboard');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Unauthorized access');
    }

    public function test_member_cannot_invite_users(): void
    {
        // Member should not have access to client creation routes
        $response = $this->actingAs($this->member)->get('/member/clients/create');

        $response->assertStatus(404);
    }

    public function test_member_can_view_url_creation_form(): void
    {
        $response = $this->actingAs($this->member)->get('/member/urls/create');

        $response->assertStatus(200);
        $response->assertViewIs('member.urls.create');
    }

    public function test_member_can_create_short_url(): void
    {
        $response = $this->actingAs($this->member)->post('/member/urls/store', [
            'long_url' => 'https://www.example.com/very/long/url/path',
        ]);

        $response->assertRedirect(route('member.dashboard.index'));
        $response->assertSessionHas('success', 'Short URL generated successfully.');

        $this->assertDatabaseHas('shorteners', [
            'long_url' => 'https://www.example.com/very/long/url/path',
            'user_id' => $this->member->id,
        ]);

        $shortener = Shortener::where('user_id', $this->member->id)->first();
        $this->assertNotNull($shortener);
        $this->assertEquals(8, strlen($shortener->short_url));
    }

    public function test_member_dashboard_shows_only_own_urls(): void
    {
        // Create member's own URL
        Shortener::create([
            'long_url' => 'https://example.com/member-url',
            'short_url' => 'mem12345',
            'user_id' => $this->member->id,
        ]);

        // Create another member's URL
        $otherMember = User::create([
            'name' => 'Other Member',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'invited_by' => $this->admin->id,
        ]);

        Shortener::create([
            'long_url' => 'https://example.com/other-url',
            'short_url' => 'oth98765',
            'user_id' => $otherMember->id,
        ]);

        $response = $this->actingAs($this->member)->get('/member/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('shortURLs', function ($shortURLs) {
            return $shortURLs->count() === 1 && $shortURLs->first()->short_url === 'mem12345';
        });
    }

    public function test_member_can_create_multiple_urls(): void
    {
        $this->actingAs($this->member)->post('/member/urls/store', [
            'long_url' => 'https://example.com/url1',
        ]);

        $this->actingAs($this->member)->post('/member/urls/store', [
            'long_url' => 'https://example.com/url2',
        ]);

        $this->actingAs($this->member)->post('/member/urls/store', [
            'long_url' => 'https://example.com/url3',
        ]);

        $this->assertEquals(3, Shortener::where('user_id', $this->member->id)->count());
    }

    public function test_member_url_creation_validates_url_format(): void
    {
        $response = $this->actingAs($this->member)->post('/member/urls/store', [
            'long_url' => 'not-a-valid-url',
        ]);

        $response->assertSessionHasErrors(['long_url']);
    }

    public function test_member_url_creation_requires_url_field(): void
    {
        $response = $this->actingAs($this->member)->post('/member/urls/store', []);

        $response->assertSessionHasErrors(['long_url']);
    }
}
