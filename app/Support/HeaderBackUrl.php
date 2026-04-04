<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * URL du lien « Retour » dans l’en-tête : index du module courant, pas toujours le tableau de bord.
 */
final class HeaderBackUrl
{
    /**
     * Noms de route → route cible (sans paramètres supplémentaires requis).
     */
    private const OVERRIDES = [
        'profile.password.edit' => 'profile.edit',
        'finances.recaps.contribution-membre' => 'finances.recaps.index',
        'secretariat.rapports-membres.impression' => 'secretariat.rapports-membres.index',
        'notifications.open' => 'notifications.index',
    ];

    public static function url(): string
    {
        $route = request()->route();
        if ($route === null) {
            return route('tableau-de-bord');
        }

        $name = $route->getName();
        if ($name === null || $name === '') {
            return route('tableau-de-bord');
        }

        if (isset(self::OVERRIDES[$name])) {
            $target = self::OVERRIDES[$name];
            if (Route::has($target)) {
                return route($target);
            }
        }

        if (preg_match('/\.(show|create|edit)$/u', $name)) {
            $indexName = preg_replace('/\.(show|create|edit)$/u', '.index', $name);
            if (Route::has($indexName)) {
                $url = self::safeRoute($indexName, $route->parameters());
                if ($url !== null) {
                    return $url;
                }
            }
        }

        if (str_starts_with($name, 'parametres.') && str_ends_with($name, '.edit') && Route::has('parametres.index')) {
            return route('parametres.index');
        }

        return route('tableau-de-bord');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private static function safeRoute(string $name, array $params): ?string
    {
        if (! Route::has($name)) {
            return null;
        }

        try {
            return route($name, $params);
        } catch (\Throwable) {
            try {
                return route($name);
            } catch (\Throwable) {
                return null;
            }
        }
    }
}
