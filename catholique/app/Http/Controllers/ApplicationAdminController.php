<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationAdminController extends Controller
{
    /**
     * Hub central : structure (paroisses), accès (utilisateurs, rôles, permissions),
     * référentiels financiers, paramètres d'affichage / PDF / connexion.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->canAccessApplicationAdministration(), 403, 'Accès refusé.');

        return view('application-admin.index');
    }
}
