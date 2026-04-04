<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaManifestTest extends TestCase
{
    public function test_manifest_webmanifest_est_public_et_json(): void
    {
        $response = $this->get('/manifest.webmanifest');

        $response->assertOk();
        $response->assertHeaderContains('content-type', 'application/manifest+json');
        $response->assertJsonStructure([
            'name',
            'short_name',
            'start_url',
            'scope',
            'icons',
        ]);
    }
}
