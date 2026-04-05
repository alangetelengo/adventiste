<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Même logique que le projet GED (AuthenticatedSessionController) :
 * formulaire login en POST classique, session régénérée après succès.
 * Spécifique Adventiste : le compte doit être lié à une église ou à la mission.
 */
class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->eglise_locale_id === null && $user->mission_id === null) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('auth.account_not_linked')]);
        }

        return redirect()->intended(route('tableau-de-bord'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
