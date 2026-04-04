<?php

namespace App\Support;

use App\Models\Bapteme;
use App\Models\District;
use App\Models\EgliseLocale;
use App\Models\GroupeMission;
use App\Models\Membre;
use App\Models\MissionReglesVentilationRecettes;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\MissionTresorerieVentilationLigne;
use App\Models\Permission;
use App\Models\RapportMembreEglise;
use App\Models\RapportMensuelEglise;
use App\Models\RapportStationMission;
use App\Models\RecapSabbatEglise;
use App\Models\Role;
use App\Models\TypeRecetteMission;
use App\Models\TypeStatutMembre;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

final class NavigationGate
{
    /**
     * Module Ventilation trésorerie mission (permission finances.ventilation_tresorerie_mission.view).
     */
    public static function peutVoirVentilationTresorerieMission(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return Gate::forUser($user)->allows('ventilationModule', MissionTresorerieRapportMensuel::class);
    }

    /**
     * Les secrétaires (église / mission) ne sont pas orientés vers les récaps du sabbat (périmètre trésorerie).
     */
    public static function peutVoirRecapsSabbatDansNavigation(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->hasRole('secretaire_eglise') || $user->hasRole('secretaire_executif_mission')) {
            return false;
        }

        return true;
    }

    /**
     * Indique si l’utilisateur peut ouvrir au moins une tuile du hub Paramètres.
     */
    public static function canAccessParametresHub(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        $checks = [
            [EgliseLocale::class],
            [District::class],
            [GroupeMission::class],
            [MissionTresorerieVentilationLigne::class],
            [TypeRecetteMission::class],
            [TypeStatutMembre::class],
            [MissionReglesVentilationRecettes::class],
            [User::class],
            [Role::class],
            [Permission::class],
        ];

        foreach ($checks as $args) {
            if (Gate::forUser($user)->allows('viewAny', $args)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie l’accès à une route nommée selon les policies (aligné sur les contrôleurs).
     */
    public static function canVisitRoute(?User $user, string $routeName): bool
    {
        if ($user === null || ! Route::has($routeName)) {
            return false;
        }

        if (str_starts_with($routeName, 'finances.ventilation-tresorerie-mission.')) {
            return self::peutVoirVentilationTresorerieMission($user);
        }

        $g = Gate::forUser($user);

        return match ($routeName) {
            'home',
            'tableau-de-bord' => true,
            'profile.edit',
            'profile.password.edit' => true,
            'notifications.index',
            'notifications.feed',
            'notifications.open',
            'notifications.read-all' => true,
            'evenements',
            'groupes' => true,
            'membres.index' => $g->allows('viewAny', Membre::class),
            'membres.create' => $g->allows('create', Membre::class),
            'baptemes.index' => $g->allows('viewAny', Bapteme::class),
            'finances.recaps.index' => self::peutVoirRecapsSabbatDansNavigation($user) && $g->allows('viewAny', RecapSabbatEglise::class),
            'finances.recaps.create' => self::peutVoirRecapsSabbatDansNavigation($user) && $g->allows('create', RecapSabbatEglise::class),
            'finances.rapports-mensuels.index' => $g->allows('viewAny', RapportMensuelEglise::class),
            'finances.rapports-station.index' => $g->allows('viewAny', RapportStationMission::class),
            'finances.rapports-station.create' => $g->allows('create', RapportStationMission::class),
            'finances.synthese-annuelle-mission.index' => $g->allows('viewAny', MissionTresorerieRapportMensuel::class),
            'finances.etat-dimes-eglises.index' => $g->allows('viewAny', MissionTresorerieRapportMensuel::class),
            'secretariat.rapports-membres.index' => $g->allows('viewAny', RapportMembreEglise::class),
            'parametres.index' => self::canAccessParametresHub($user),
            'parametres.eglises.index' => $g->allows('viewAny', EgliseLocale::class),
            'parametres.groupes-mission.index' => $g->allows('viewAny', GroupeMission::class),
            'parametres.types-recette.index' => $g->allows('viewAny', TypeRecetteMission::class),
            'parametres.utilisateurs.index' => $g->allows('viewAny', User::class),
            'parametres.permissions.index' => $g->allows('viewAny', Permission::class),
            'parametres.roles.index' => $g->allows('viewAny', Role::class),
            default => false,
        };
    }
}
