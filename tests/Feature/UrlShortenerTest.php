<?php

namespace Tests\Feature;

use App\Models\Shortener;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlShortenerTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_short_url_is_generated_with_valid_long_url(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'https://www.example.com/very/long/url/path',
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));

        $shortener = Shortener::where('user_id', $this->admin->id)->first();
        $this->assertNotNull($shortener);
        $this->assertEquals('https://www.example.com/very/long/url/path', $shortener->long_url);
    }

    public function test_short_url_is_exactly_eight_characters(): void
    {
        $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'https://www.example.com/test',
        ]);

        $shortener = Shortener::where('user_id', $this->admin->id)->first();
        $this->assertEquals(8, strlen($shortener->short_url));
    }

    public function test_short_url_is_lowercase(): void
    {
        $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'https://www.example.com/test',
        ]);

        $shortener = Shortener::where('user_id', $this->admin->id)->first();
        $this->assertEquals(strtolower($shortener->short_url), $shortener->short_url);
    }

    public function test_short_url_is_unique(): void
    {
        // Create first URL
        $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'https://www.example.com/first',
        ]);

        // Create second URL
        $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'https://www.example.com/second',
        ]);

        $shorteners = Shortener::where('user_id', $this->admin->id)->get();
        $this->assertEquals(2, $shorteners->count());
        $this->assertNotEquals($shorteners[0]->short_url, $shorteners[1]->short_url);
    }

    public function test_validation_error_for_invalid_url(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'not-a-valid-url',
        ]);

        $response->assertSessionHasErrors(['long_url']);
    }

    public function test_validation_error_for_missing_url(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/urls/store', []);

        $response->assertSessionHasErrors(['long_url']);
    }

    public function test_multiple_urls_can_be_created_without_collision(): void
    {
        // Create 20 URLs to test collision handling
        for ($i = 1; $i <= 20; $i++) {
            $this->actingAs($this->admin)->post('/admin/urls/store', [
                'long_url' => "https://www.example.com/url{$i}",
            ]);
        }

        $shorteners = Shortener::where('user_id', $this->admin->id)->get();
        $this->assertEquals(20, $shorteners->count());

        // Check all short URLs are unique
        $shortUrls = $shorteners->pluck('short_url')->toArray();
        $uniqueShortUrls = array_unique($shortUrls);
        $this->assertCount(20, $uniqueShortUrls);
    }

    public function test_same_long_url_can_have_different_short_urls(): void
    {
        // Create same URL twice
        $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'https://www.example.com/duplicate',
        ]);

        $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'https://www.example.com/duplicate',
        ]);

        $shorteners = Shortener::where('long_url', 'https://www.example.com/duplicate')->get();
        $this->assertEquals(2, $shorteners->count());
        $this->assertNotEquals($shorteners[0]->short_url, $shorteners[1]->short_url);
    }

    public function test_url_accepts_http_protocol(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'http://www.example.com/test',
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));
        $this->assertDatabaseHas('shorteners', [
            'long_url' => 'http://www.example.com/test',
        ]);
    }

    public function test_url_accepts_https_protocol(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => 'https://www.example.com/test',
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));
        $this->assertDatabaseHas('shorteners', [
            'long_url' => 'https://www.example.com/test',
        ]);
    }

    public function test_url_with_query_parameters_is_accepted(): void
    {
        $longUrl = 'https://www.example.com/page?param1=value1&param2=value2';

        $response = $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => $longUrl,
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));
        $this->assertDatabaseHas('shorteners', [
            'long_url' => $longUrl,
        ]);
    }

    public function test_url_with_fragment_is_accepted(): void
    {
        $longUrl = 'https://www.example.com/page#section';

        $response = $this->actingAs($this->admin)->post('/admin/urls/store', [
            'long_url' => $longUrl,
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));
        $this->assertDatabaseHas('shorteners', [
            'long_url' => $longUrl,
        ]);
    }
}
