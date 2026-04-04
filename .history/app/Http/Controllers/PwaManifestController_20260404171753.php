<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $data = [
            'name' => config('app.name').' — '.config('app.denomination'),
            'short_name' => config('app.name'),
            'description' => 'SDA — Seven-day Adventist — gestion paroissiale (hors ligne partiel)',
            'start_url' => url('/'),
            'scope' => url('/'),
            'display' => 'standalone',
            'background_color' => '#0f172a',
            'theme_color' => '#0f172a',
            'lang' => 'fr',
            'icons' => [
                [
                    'src' => asset('images/logo_sda.png'),
                    'sizes' => '192x192',
                    'type' => 'image/jpeg',
                    'purpose' => 'any',
                ],
            ],
        ];

        return response()->json($data, 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
