<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_redirects_authenticated_users_to_the_profile_page()
    {
        $this->actingAs(User::factory()->create());

        $this->get('/settings')->assertRedirect(route('profile.edit'));
    }

    public function test_settings_redirects_guests_to_the_login_page()
    {
        $this->get('/settings')->assertRedirect(route('login'));
    }

    public function test_settings_only_answers_to_get_requests()
    {
        // Evita volver a registrar el verbo QUERY, que Wayfinder aun no tipa.
        $this->actingAs(User::factory()->create());

        $this->call('QUERY', '/settings')->assertStatus(405);
        $this->post('/settings')->assertStatus(405);
    }
}
