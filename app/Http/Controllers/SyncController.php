<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    /**
     * Fournit un jeton CSRF frais pour rejouer les requêtes mises en file hors ligne.
     */
    public function csrf(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }
}
