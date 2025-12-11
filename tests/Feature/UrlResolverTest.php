<?php

namespace Tests\Feature;

use App\Models\Shortener;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_short_url_redirects_to_long_url(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        $shortener = Shortener::create([
            'long_url' => 'https://www.example.com/destination',
            'short_url' => 'abc12345',
            'user_id' => $user->id,
        ]);

        $response = $this->get('/abc12345');

        $response->assertRedirect('https://www.example.com/destination');
        $response->assertStatus(302);
    }

    public function test_invalid_short_url_returns_404(): void
    {
        $response = $this->get('/nonexist');

        $response->assertStatus(404);
    }

    public function test_short_url_can_be_accessed_without_authentication(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        Shortener::create([
            'long_url' => 'https://www.example.com/public',
            'short_url' => 'pub12345',
            'user_id' => $user->id,
        ]);

        // Don't authenticate - test as guest
        $response = $this->get('/pub12345');

        $response->assertRedirect('https://www.example.com/public');
    }

    public function test_redirect_uses_proper_http_status_code(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        Shortener::create([
            'long_url' => 'https://www.example.com/test',
            'short_url' => 'test1234',
            'user_id' => $user->id,
        ]);

        $response = $this->get('/test1234');

        // Check for 302 redirect (temporary redirect)
        $response->assertStatus(302);
    }

    public function test_case_sensitive_short_url_resolution(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        Shortener::create([
            'long_url' => 'https://www.example.com/test',
            'short_url' => 'abc12345',
            'user_id' => $user->id,
        ]);

        // Test exact match
        $response = $this->get('/abc12345');
        $response->assertRedirect('https://www.example.com/test');

        // Since short URLs are lowercase, uppercase should not match
        $response = $this->get('/ABC12345');
        $response->assertStatus(404);
    }

    public function test_url_with_query_parameters_redirects_correctly(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        $longUrl = 'https://www.example.com/page?param1=value1&param2=value2';

        Shortener::create([
            'long_url' => $longUrl,
            'short_url' => 'query123',
            'user_id' => $user->id,
        ]);

        $response = $this->get('/query123');

        $response->assertRedirect($longUrl);
    }

    public function test_url_with_fragment_redirects_correctly(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        $longUrl = 'https://www.example.com/page#section';

        Shortener::create([
            'long_url' => $longUrl,
            'short_url' => 'frag1234',
            'user_id' => $user->id,
        ]);

        $response = $this->get('/frag1234');

        $response->assertRedirect($longUrl);
    }

    public function test_multiple_short_urls_resolve_to_correct_destinations(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
        ]);

        Shortener::create([
            'long_url' => 'https://www.google.com',
            'short_url' => 'google12',
            'user_id' => $user->id,
        ]);

        Shortener::create([
            'long_url' => 'https://www.github.com',
            'short_url' => 'github12',
            'user_id' => $user->id,
        ]);

        Shortener::create([
            'long_url' => 'https://www.laravel.com',
            'short_url' => 'laravel2',
            'user_id' => $user->id,
        ]);

        $this->get('/google12')->assertRedirect('https://www.google.com');
        $this->get('/github12')->assertRedirect('https://www.github.com');
        $this->get('/laravel2')->assertRedirect('https://www.laravel.com');
    }
}
