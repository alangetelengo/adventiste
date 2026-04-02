<?php

namespace App\Providers;

use App\Models\District;
use App\Models\GroupeMission;
use App\Models\Membre;
use App\Models\User;
use App\Models\EgliseLocale;
use App\Models\DepartementMinistere;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\RecapSabbatEglise;
use App\Observers\LigneDimeOffrandeRecapObserver;
use App\Observers\RecapSabbatEgliseObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        RecapSabbatEglise::observe(RecapSabbatEgliseObserver::class);
        LigneDimeOffrandeRecap::observe(LigneDimeOffrandeRecapObserver::class);

        Route::bind('groupe', function (string $value) {
            $user = Auth::user();
            if (! $user || ! $user->mission_id) {
                abort(403);
            }

            $groupe = GroupeMission::query()
                ->where('mission_id', $user->mission_id)
                ->whereKey($value)
                ->first();

            if (! $groupe) {
                abort(404);
            }

            return $groupe;
        });

        Route::bind('district', function (string $value) {
            $user = Auth::user();
            if (! $user || ! $user->mission_id) {
                abort(403);
            }

            $district = District::query()
                ->where('mission_id', $user->mission_id)
                ->whereKey($value)
                ->first();

            if (! $district) {
                abort(404);
            }

            return $district;
        });

        Route::bind('utilisateur', function (string $value) {
            $actor = Auth::user();
            if (! $actor || ! $actor->mission_id) {
                abort(403);
            }

            $model = User::query()
                ->where('mission_id', $actor->mission_id)
                ->whereKey($value)
                ->first();

            if (! $model) {
                abort(404);
            }

            return $model;
        });

        Route::bind('membre', function (string $value) {
            $user = Auth::user();
            if (! $user) {
                abort(403);
            }

            $query = Membre::query()->whereKey($value);

            if ($user->eglise_locale_id !== null) {
                $query->where('eglise_locale_id', $user->eglise_locale_id);
            } elseif ($user->mission_id !== null) {
                $query->whereHas('egliseLocale', fn($q) => $q->where('mission_id', $user->mission_id));
            } else {
                abort(403);
            }

            $membre = $query->first();

            if (! $membre) {
                abort(404);
            }

            return $membre;
        });

        Route::bind('eglise', function (string $value) {
            $user = Auth::user();
            if (! $user || ! $user->mission_id) {
                abort(403);
            }

            $eglise = EgliseLocale::query()
                ->where('mission_id', $user->mission_id)
                ->whereKey($value)
                ->first();

            if (! $eglise) {
                abort(404);
            }

            return $eglise;
        });

        Route::bind('departement', function (string $value) {
            $user = Auth::user();
            if (! $user || ! $user->mission_id) {
                abort(403);
            }

            $departement = DepartementMinistere::query()
                ->whereHas('egliseLocale', fn($q) => $q->where('mission_id', $user->mission_id))
                ->whereKey($value)
                ->first();

            if (! $departement) {
                abort(404);
            }

            return $departement;
        });
    }
}
