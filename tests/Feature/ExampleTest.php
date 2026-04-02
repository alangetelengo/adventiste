<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_la_racine_redirige_les_visiteurs_vers_la_connexion(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
