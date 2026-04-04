<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncCsrfTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_csrf_redirige_les_invites(): void
    {
        $this->get(route('sync.csrf'))->assertRedirect(route('login'));
    }

    public function test_sync_csrf_retourne_un_jeton_json_pour_utilisateur_connecte(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('sync.csrf'));

        $response->assertOk();
        $response->assertJsonStructure(['token']);
        $this->assertNotEmpty($response->json('token'));
    }
}
