<?php

namespace App\Http\Controllers;

use App\Support\NavigationGate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParametresController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user === null || ! NavigationGate::canAccessParametresHub($user)) {
            abort(403);
        }

        return view('parametres.index');
    }
}
