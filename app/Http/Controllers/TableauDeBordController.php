<?php

namespace App\Http\Controllers;

use App\Models\Bapteme;
use App\Models\EgliseLocale;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\Membre;
use App\Models\RapportMembreEglise;
use App\Models\RapportMensuelEglise;
use App\Models\RecapSabbatEglise;
use App\Models\User;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\MissionTresorerieTransfertBancaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TableauDeBordController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $stats = $this->statsPour($user);
        $roleStats = $this->statsParRole($user);
        $dashboard = $this->dashboardV2Data($user);

        return view('tableau-de-bord', compact('stats', 'roleStats', 'dashboard'));
    }

    /**
     * @return array{recaps_total: int, recaps_brouillon: int, recaps_ce_mois: int, rapports_total: int, rapports_non_verrouilles: int, eglises_actives: int|null, membres_total: int}
     */
    private function statsPour(User $user): array
    {
        $defaults = [
            'recaps_total' => 0,
            'recaps_brouillon' => 0,
            'recaps_ce_mois' => 0,
            'rapports_total' => 0,
            'rapports_non_verrouilles' => 0,
            'eglises_actives' => null,
            'membres_total' => 0,
        ];

        if ($user->eglise_locale_id === null && $user->mission_id === null) {
            return $defaults;
        }

        $recapBase = $this->recapsScopes($user);
        $rapportBase = $this->rapportsScopes($user);

        $now = now();

        $eglisesActives = null;
        if ($user->mission_id !== null && $user->eglise_locale_id === null) {
            $eglisesActives = EgliseLocale::query()
                ->where('mission_id', $user->mission_id)
                ->where('actif', true)
                ->count();
        }

        return [
            'recaps_total' => (clone $recapBase)->count(),
            'recaps_brouillon' => (clone $recapBase)->where('statut', 'brouillon')->count(),
            'recaps_ce_mois' => (clone $recapBase)
                ->whereYear('date_sabbat', $now->year)
                ->whereMonth('date_sabbat', $now->month)
                ->count(),
            'rapports_total' => (clone $rapportBase)->count(),
            'rapports_non_verrouilles' => (clone $rapportBase)->whereNull('verrouille_le')->count(),
            'eglises_actives' => $eglisesActives,
            'membres_total' => (clone $this->membresScopes($user))->count(),
        ];
    }

    private function membresScopes(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $q = Membre::query();

        if ($user->eglise_locale_id !== null) {
            $q->where('eglise_locale_id', $user->eglise_locale_id);
        } elseif ($user->mission_id !== null) {
            $q->whereHas('egliseLocale', fn($q2) => $q2->where('mission_id', $user->mission_id));
        } else {
            $q->whereRaw('1 = 0');
        }

        return $q;
    }

    private function recapsScopes(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $q = RecapSabbatEglise::query();

        if ($user->eglise_locale_id !== null) {
            $q->where('eglise_locale_id', $user->eglise_locale_id);
        } elseif ($user->mission_id !== null) {
            $q->whereHas('egliseLocale', fn($q2) => $q2->where('mission_id', $user->mission_id));
        } else {
            $q->whereRaw('1 = 0');
        }

        return $q;
    }

    private function rapportsScopes(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $q = RapportMensuelEglise::query();

        if ($user->eglise_locale_id !== null) {
            $q->where('eglise_locale_id', $user->eglise_locale_id);
        } elseif ($user->mission_id !== null) {
            $q->whereHas('egliseLocale', fn($q2) => $q2->where('mission_id', $user->mission_id));
        } else {
            $q->whereRaw('1 = 0');
        }

        return $q;
    }

    /**
     * @return array{role_name: string, role_label: string, cards: array, actions: array}
     */
    private function statsParRole(User $user): array
    {
        $role = $user->role?->name ?? 'unknown';

        return match ($role) {
            'secretaire_eglise' => $this->statsSecretaireEglise($user),
            'tresorier_eglise' => $this->statsTresorierEglise($user),
            'secretaire_executif_mission' => $this->statsSecretaireMission($user),
            'tresorier_mission' => $this->statsTresorierMission($user),
            'president_mission' => $this->statsPresidentMission($user),
            'admin_mission' => $this->statsAdminMission($user),
            default => $this->statsDefault($user),
        };
    }

    private function statsSecretaireEglise(User $user): array
    {
        return [
            'role_name' => 'secretaire_eglise',
            'role_label' => 'Secrétaire d\'église locale',
            'cards' => [
                [
                    'title' => 'Membres de l\'église',
                    'description' => 'Gestion des fiches membres et suivi des adhésions',
                    'icon' => 'users',
                    'route' => 'membres.index',
                    'stats' => [
                        'total' => $this->membresScopes($user)->count(),
                        'actifs' => $this->membresScopes($user)->where('actif', true)->count(),
                    ],
                    'color' => 'blue',
                ],
                [
                    'title' => 'Récaps du sabbat',
                    'description' => 'Saisie des dîmes et offrandes hebdomadaires',
                    'icon' => 'currency-dollar',
                    'route' => 'finances.recaps.index',
                    'stats' => [
                        'ce_mois' => $this->recapsScopes($user)->whereYear('date_sabbat', now()->year)->whereMonth('date_sabbat', now()->month)->count(),
                        'brouillons' => $this->recapsScopes($user)->where('statut', 'brouillon')->count(),
                    ],
                    'color' => 'green',
                ],
            ],
            'actions' => [
                ['label' => 'Nouveau membre', 'route' => 'membres.create', 'icon' => 'plus'],
                ['label' => 'Nouveau récap', 'route' => 'finances.recaps.create', 'icon' => 'document-plus'],
            ],
        ];
    }

    private function statsTresorierEglise(User $user): array
    {
        return [
            'role_name' => 'tresorier_eglise',
            'role_label' => 'Trésorier d\'église locale',
            'cards' => [
                [
                    'title' => 'Récaps du sabbat',
                    'description' => 'Validation et suivi des finances hebdomadaires',
                    'icon' => 'currency-dollar',
                    'route' => 'finances.recaps.index',
                    'stats' => [
                        'total' => $this->recapsScopes($user)->count(),
                        'a_valider' => $this->recapsScopes($user)->where('statut', 'soumis')->count(),
                        'ce_mois' => $this->recapsScopes($user)->whereYear('date_sabbat', now()->year)->whereMonth('date_sabbat', now()->month)->count(),
                    ],
                    'color' => 'green',
                ],
                [
                    'title' => 'Rapports mensuels',
                    'description' => 'Synthèse financière mensuelle de l\'église',
                    'icon' => 'chart-bar',
                    'route' => 'finances.rapports-mensuels.index',
                    'stats' => [
                        'total' => $this->rapportsScopes($user)->count(),
                        'non_verrouilles' => $this->rapportsScopes($user)->whereNull('verrouille_le')->count(),
                    ],
                    'color' => 'purple',
                ],
            ],
            'actions' => [
                ['label' => 'Nouveau récap', 'route' => 'finances.recaps.create', 'icon' => 'plus'],
                ['label' => 'Voir rapports', 'route' => 'finances.rapports-mensuels.index', 'icon' => 'eye'],
            ],
        ];
    }

    private function statsSecretaireMission(User $user): array
    {
        return [
            'role_name' => 'secretaire_executif_mission',
            'role_label' => 'Secrétaire exécutif de mission',
            'cards' => [
                [
                    'title' => 'Vue d\'ensemble mission',
                    'description' => 'Tableau consolidé de toutes les églises',
                    'icon' => 'building-office',
                    'route' => 'tableau-de-bord',
                    'stats' => [
                        'eglises' => \App\Models\EgliseLocale::where('mission_id', $user->mission_id)->where('actif', true)->count(),
                        'membres_total' => $this->membresScopes($user)->count(),
                    ],
                    'color' => 'indigo',
                ],
                [
                    'title' => 'Rapports consolidés',
                    'description' => 'Synthèse mensuelle de la mission',
                    'icon' => 'chart-bar',
                    'route' => 'finances.rapports-station.index',
                    'stats' => [
                        'total' => \App\Models\RapportStationMission::where('mission_id', $user->mission_id)->count(),
                        'ce_mois' => \App\Models\RapportStationMission::where('mission_id', $user->mission_id)->where('annee', now()->year)->where('mois', now()->month)->count(),
                    ],
                    'color' => 'purple',
                ],
            ],
            'actions' => [
                ['label' => 'Gérer églises', 'route' => 'parametres.eglises.index', 'icon' => 'building-storefront'],
                ['label' => 'Gérer membres', 'route' => 'membres.index', 'icon' => 'users'],
                ['label' => 'Rapports station', 'route' => 'finances.rapports-station.index', 'icon' => 'document-chart-bar'],
            ],
        ];
    }

    private function statsTresorierMission(User $user): array
    {
        return [
            'role_name' => 'tresorier_mission',
            'role_label' => 'Trésorier de mission',
            'cards' => [
                [
                    'title' => 'Finances consolidées',
                    'description' => 'Vue d\'ensemble des finances de la mission',
                    'icon' => 'banknotes',
                    'route' => 'finances.rapports-station.index',
                    'stats' => [
                        'rapports_total' => \App\Models\RapportStationMission::where('mission_id', $user->mission_id)->count(),
                        'rapports_mois' => \App\Models\RapportStationMission::where('mission_id', $user->mission_id)->where('annee', now()->year)->where('mois', now()->month)->count(),
                    ],
                    'color' => 'emerald',
                ],
                [
                    'title' => 'Validation récaps',
                    'description' => 'Récaps soumis en attente de validation',
                    'icon' => 'check-circle',
                    'route' => 'finances.recaps.index',
                    'stats' => [
                        'a_valider' => $this->recapsScopes($user)->where('statut', 'soumis')->count(),
                        'verrouilles' => $this->recapsScopes($user)->where('statut', 'verrouille')->count(),
                    ],
                    'color' => 'blue',
                ],
                [
                    'title' => 'Groupes mission',
                    'description' => 'Gestion des groupes et de leurs finances',
                    'icon' => 'user-group',
                    'route' => 'parametres.groupes-mission.index',
                    'stats' => [
                        'total' => \App\Models\GroupeMission::where('mission_id', $user->mission_id)->count(),
                        'actifs' => \App\Models\GroupeMission::where('mission_id', $user->mission_id)->where('actif', true)->count(),
                    ],
                    'color' => 'orange',
                ],
            ],
            'actions' => [
                ['label' => 'Nouveau rapport station', 'route' => 'finances.rapports-station.create', 'icon' => 'plus'],
                ['label' => 'Gérer groupes', 'route' => 'parametres.groupes-mission.index', 'icon' => 'user-group'],
                ['label' => 'Types de recette', 'route' => 'parametres.types-recette.index', 'icon' => 'tag'],
            ],
        ];
    }

    private function statsPresidentMission(User $user): array
    {
        return [
            'role_name' => 'president_mission',
            'role_label' => 'Président de mission',
            'cards' => [
                [
                    'title' => 'Vue stratégique',
                    'description' => 'Tableau de bord général de la mission',
                    'icon' => 'presentation-chart-line',
                    'route' => 'tableau-de-bord',
                    'stats' => [
                        'eglises_actives' => \App\Models\EgliseLocale::where('mission_id', $user->mission_id)->where('actif', true)->count(),
                        'membres_total' => $this->membresScopes($user)->count(),
                        'districts' => \App\Models\District::where('mission_id', $user->mission_id)->count(),
                    ],
                    'color' => 'slate',
                ],
                [
                    'title' => 'État des finances',
                    'description' => 'Suivi global des finances mission',
                    'icon' => 'currency-dollar',
                    'route' => 'finances.rapports-station.index',
                    'stats' => [
                        'rapports_ce_mois' => \App\Models\RapportStationMission::where('mission_id', $user->mission_id)->where('annee', now()->year)->where('mois', now()->month)->count(),
                        'recaps_ce_mois' => $this->recapsScopes($user)->whereYear('date_sabbat', now()->year)->whereMonth('date_sabbat', now()->month)->count(),
                    ],
                    'color' => 'green',
                ],
                [
                    'title' => 'Administration',
                    'description' => 'Gestion des utilisateurs et permissions',
                    'icon' => 'cog-6-tooth',
                    'route' => 'parametres.utilisateurs.index',
                    'stats' => [
                        'utilisateurs' => \App\Models\User::where('mission_id', $user->mission_id)->count(),
                        'roles_actifs' => \App\Models\Role::whereHas('users', fn($q) => $q->where('mission_id', $user->mission_id))->count(),
                    ],
                    'color' => 'gray',
                ],
            ],
            'actions' => [
                ['label' => 'Gérer utilisateurs', 'route' => 'parametres.utilisateurs.index', 'icon' => 'users'],
                ['label' => 'Paramètres mission', 'route' => 'parametres.index', 'icon' => 'cog-6-tooth'],
                ['label' => 'Rapports globaux', 'route' => 'finances.rapports-station.index', 'icon' => 'document-chart-bar'],
            ],
        ];
    }

    private function statsAdminMission(User $user): array
    {
        return [
            'role_name' => 'admin_mission',
            'role_label' => 'Administrateur de mission',
            'cards' => [
                [
                    'title' => 'Administration complète',
                    'description' => 'Contrôle total de la mission',
                    'icon' => 'shield-check',
                    'route' => 'parametres.index',
                    'stats' => [
                        'utilisateurs_total' => \App\Models\User::where('mission_id', $user->mission_id)->count(),
                        'permissions_total' => \App\Models\Permission::count(),
                        'roles_total' => \App\Models\Role::count(),
                    ],
                    'color' => 'red',
                ],
                [
                    'title' => 'Données financières',
                    'description' => 'Vue complète sur toutes les finances',
                    'icon' => 'banknotes',
                    'route' => 'finances.rapports-station.index',
                    'stats' => [
                        'tous_recaps' => $this->recapsScopes($user)->count(),
                        'tous_rapports' => \App\Models\RapportStationMission::where('mission_id', $user->mission_id)->count(),
                    ],
                    'color' => 'emerald',
                ],
                [
                    'title' => 'Structure mission',
                    'description' => 'Gestion complète de l\'organisation',
                    'icon' => 'building-office',
                    'route' => 'parametres.eglises.index',
                    'stats' => [
                        'eglises_total' => \App\Models\EgliseLocale::where('mission_id', $user->mission_id)->count(),
                        'districts_total' => \App\Models\District::where('mission_id', $user->mission_id)->count(),
                        'groupes_total' => \App\Models\GroupeMission::where('mission_id', $user->mission_id)->count(),
                    ],
                    'color' => 'blue',
                ],
            ],
            'actions' => [
                ['label' => 'Administration', 'route' => 'parametres.index', 'icon' => 'cog-6-tooth'],
                ['label' => 'Gérer utilisateurs', 'route' => 'parametres.utilisateurs.index', 'icon' => 'users'],
                ['label' => 'Permissions', 'route' => 'parametres.permissions.index', 'icon' => 'key'],
                ['label' => 'Rôles', 'route' => 'parametres.roles.index', 'icon' => 'user-circle'],
            ],
        ];
    }

    private function statsDefault(User $user): array
    {
        return [
            'role_name' => 'default',
            'role_label' => 'Utilisateur',
            'cards' => [
                [
                    'title' => 'Mes données',
                    'description' => 'Accès à vos informations personnelles',
                    'icon' => 'user',
                    'route' => 'profile.edit',
                    'stats' => [],
                    'color' => 'gray',
                ],
            ],
            'actions' => [],
        ];
    }

    /**
     * @return array{
     *   periode:string,
     *   kpis:array<string,int|float>,
     *   recapsRecents:\Illuminate\Support\Collection<int,\App\Models\RecapSabbatEglise>,
     *   rapportsSoumis:\Illuminate\Support\Collection<int,\App\Models\RapportMensuelEglise>
     * }
     */
    private function dashboardV2Data(User $user): array
    {
        $dashboard = match ($user->role?->name) {
            'secretaire_eglise' => $this->dashboardSecretaireEglise($user),
            'tresorier_eglise' => $this->dashboardTresorierEglise($user),
            'president_mission' => $this->dashboardPresidentMission($user),
            default => $this->dashboardParDefaut($user),
        };

        $dashboard['priorites'] = $this->prioritesPour($user);
        $dashboard['comparatifMensuel'] = $this->comparatifMensuelPour($user);

        return $dashboard;
    }

    /**
     * @return array{
     *   role:string,
     *   periode:string,
     *   kpis:list<array{label:string,value:int|float,suffix:string}>,
     *   recapsRecents:\Illuminate\Support\Collection<int,\App\Models\RecapSabbatEglise>,
     *   rapportsSoumis:\Illuminate\Support\Collection<int,\App\Models\RapportMensuelEglise>
     * }
     */
    private function dashboardTresorierEglise(User $user): array
    {
        $now = now();
        $recapBase = $this->recapsScopes($user);
        $rapportBase = $this->rapportsScopes($user);

        $recapsMois = (clone $recapBase)
            ->whereYear('date_sabbat', $now->year)
            ->whereMonth('date_sabbat', $now->month);

        $recapIdsMois = (clone $recapsMois)->pluck('id');
        $recettesMois = 0.0;
        if ($recapIdsMois->isNotEmpty()) {
            $recettesMois = (float) LigneDimeOffrandeRecap::query()
                ->whereIn('recap_sabbat_eglise_id', $recapIdsMois)
                ->selectRaw('COALESCE(SUM(dimes + offrandes),0) as total')
                ->value('total');
        }

        $rapportMois = (clone $rapportBase)
            ->where('annee', $now->year)
            ->where('mois', $now->month);

        $kpis = [
            'recettes_mois' => (int) round($recettesMois),
            'a_transferer_mission' => (int) round((float) (clone $rapportMois)->sum('total_a_transferer_mission_mois')),
            'recaps_saisis_mois' => (int) (clone $recapsMois)->count(),
            'rapports_soumis' => (int) (clone $rapportBase)->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)->count(),
        ];

        $recapsRecents = (clone $recapBase)
            ->with('egliseLocale')
            ->withSum('lignesContributions as total_dimes', 'dimes')
            ->withSum('lignesContributions as total_offrandes', 'offrandes')
            ->latest('date_sabbat')
            ->limit(6)
            ->get();

        $rapportsSoumis = (clone $rapportBase)
            ->with('egliseLocale')
            ->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)
            ->orderByDesc('soumis_le')
            ->limit(6)
            ->get();

        return [
            'role' => 'tresorier_eglise',
            'periode' => $now->translatedFormat('F Y'),
            'kpis' => [
                ['label' => 'Recettes du mois', 'value' => (int) $kpis['recettes_mois'], 'suffix' => 'FCFA'],
                ['label' => 'À transférer mission', 'value' => (int) $kpis['a_transferer_mission'], 'suffix' => 'FCFA'],
                ['label' => 'Récaps saisis', 'value' => (int) $kpis['recaps_saisis_mois'], 'suffix' => ''],
                ['label' => 'Rapports soumis', 'value' => (int) $kpis['rapports_soumis'], 'suffix' => ''],
            ],
            'recapsRecents' => $recapsRecents,
            'rapportsSoumis' => $rapportsSoumis,
        ];
    }

    /**
     * @return array{
     *   role:string,
     *   periode:string,
     *   kpis:list<array{label:string,value:int|float,suffix:string}>,
     *   rapportsMembresRecents:\Illuminate\Support\Collection<int,\App\Models\RapportMembreEglise>,
     *   baptemesRecents:\Illuminate\Support\Collection<int,\App\Models\Bapteme>
     * }
     */
    private function dashboardSecretaireEglise(User $user): array
    {
        $now = now();
        $membresBase = $this->membresScopes($user);
        $egliseId = (int) ($user->eglise_locale_id ?? 0);

        $rapportsMembresRecents = RapportMembreEglise::query()
            ->where('eglise_locale_id', $egliseId)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $baptemesRecents = Bapteme::query()
            ->where('eglise_locale_id', $egliseId)
            ->orderByDesc('date_bapteme')
            ->limit(6)
            ->get();

        return [
            'role' => 'secretaire_eglise',
            'periode' => $now->translatedFormat('F Y'),
            'kpis' => [
                ['label' => 'Membres total', 'value' => (int) (clone $membresBase)->count(), 'suffix' => ''],
                ['label' => 'Membres actifs', 'value' => (int) (clone $membresBase)->where('actif', true)->count(), 'suffix' => ''],
                ['label' => 'Baptêmes (mois)', 'value' => (int) Bapteme::query()->where('eglise_locale_id', $egliseId)->whereYear('date_bapteme', $now->year)->whereMonth('date_bapteme', $now->month)->count(), 'suffix' => ''],
                ['label' => 'Rapports membres soumis', 'value' => (int) RapportMembreEglise::query()->where('eglise_locale_id', $egliseId)->where('etat', RapportMembreEglise::ETAT_SOUMIS)->count(), 'suffix' => ''],
            ],
            'rapportsMembresRecents' => $rapportsMembresRecents,
            'baptemesRecents' => $baptemesRecents,
        ];
    }

    /**
     * @return array{
     *   role:string,
     *   periode:string,
     *   kpis:list<array{label:string,value:int|float,suffix:string}>,
     *   rapportsFinancesMission:\Illuminate\Support\Collection<int,\App\Models\RapportMensuelEglise>,
     *   rapportsMembresMission:\Illuminate\Support\Collection<int,\App\Models\RapportMembreEglise>
     * }
     */
    private function dashboardPresidentMission(User $user): array
    {
        $now = now();
        $missionId = (int) ($user->mission_id ?? 0);

        $rapportsFinancesMission = RapportMensuelEglise::query()
            ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
            ->with('egliseLocale')
            ->orderByDesc('soumis_le')
            ->limit(8)
            ->get();

        $rapportsMembresMission = RapportMembreEglise::query()
            ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
            ->with('egliseLocale')
            ->orderByDesc('soumis_le')
            ->limit(8)
            ->get();

        return [
            'role' => 'president_mission',
            'periode' => $now->translatedFormat('F Y'),
            'kpis' => [
                ['label' => 'Églises actives', 'value' => (int) EgliseLocale::query()->where('mission_id', $missionId)->where('actif', true)->count(), 'suffix' => ''],
                ['label' => 'Membres mission', 'value' => (int) $this->membresScopes($user)->count(), 'suffix' => ''],
                ['label' => 'Rapports finances soumis', 'value' => (int) RapportMensuelEglise::query()->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)->count(), 'suffix' => ''],
                ['label' => 'Rapports membres soumis', 'value' => (int) RapportMembreEglise::query()->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))->where('etat', RapportMembreEglise::ETAT_SOUMIS)->count(), 'suffix' => ''],
            ],
            'rapportsFinancesMission' => $rapportsFinancesMission,
            'rapportsMembresMission' => $rapportsMembresMission,
        ];
    }

    /**
     * @return array{
     *   role:string,
     *   periode:string,
     *   kpis:list<array{label:string,value:int|float,suffix:string}>
     * }
     */
    private function dashboardParDefaut(User $user): array
    {
        return [
            'role' => 'default',
            'periode' => now()->translatedFormat('F Y'),
            'kpis' => [
                ['label' => 'Rapports total', 'value' => (int) $this->rapportsScopes($user)->count(), 'suffix' => ''],
                ['label' => 'Membres total', 'value' => (int) $this->membresScopes($user)->count(), 'suffix' => ''],
            ],
        ];
    }

    /**
     * @return list<array{label:string,value:int,route:string,tone:string,help:string,icon:string}>
     */
    private function prioritesPour(User $user): array
    {
        $now = now();

        if ($user->mission_id !== null && $user->eglise_locale_id === null) {
            $missionId = (int) $user->mission_id;

            return $this->trierPriorites([
                [
                    'label' => 'Transferts non rapprochés',
                    'value' => $this->transfertsNonRapproches($missionId, (int) $now->year, (int) $now->month),
                    'route' => 'finances.synthese-annuelle-mission.index',
                    'tone' => 'amber',
                    'help' => 'Mois avec écart entre transfert attendu et montant bancaire saisi.',
                    'icon' => 'banknotes',
                ],
                [
                    'label' => 'Rapports églises en retard',
                    'value' => $this->rapportsEglisesEnRetard($missionId, (int) $now->year, (int) $now->month),
                    'route' => 'finances.rapports-mensuels.index',
                    'tone' => 'rose',
                    'help' => 'Églises n’ayant pas encore soumis/validé le rapport mensuel de la période.',
                    'icon' => 'document-chart-bar',
                ],
                [
                    'label' => 'Rapports soumis à valider',
                    'value' => (int) RapportMensuelEglise::query()
                        ->where('annee', (int) $now->year)
                        ->where('mois', (int) $now->month)
                        ->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)
                        ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
                        ->count(),
                    'route' => 'finances.rapports-mensuels.index',
                    'tone' => 'indigo',
                    'help' => 'Rapports mensuels en attente d’action mission.',
                    'icon' => 'check-circle',
                ],
            ]);
        }

        $rapportMoisCount = (int) $this->rapportsScopes($user)
            ->where('annee', (int) $now->year)
            ->where('mois', (int) $now->month)
            ->count();

        $rapportRetard = $rapportMoisCount === 0
            ? 1
            : (int) $this->rapportsScopes($user)
                ->where('annee', (int) $now->year)
                ->where('mois', (int) $now->month)
                ->whereIn('etat_transmission', [
                    RapportMensuelEglise::ETAT_BROUILLON,
                    RapportMensuelEglise::ETAT_REFUSE_MISSION,
                ])
                ->count();

        return $this->trierPriorites([
            [
                'label' => 'Récaps brouillons',
                'value' => (int) $this->recapsScopes($user)->where('statut', 'brouillon')->count(),
                'route' => 'finances.recaps.index',
                'tone' => 'amber',
                'help' => 'Récaps encore non finalisés.',
                'icon' => 'currency-dollar',
            ],
            [
                'label' => 'Récaps soumis à traiter',
                'value' => (int) $this->recapsScopes($user)->where('statut', 'soumis')->count(),
                'route' => 'finances.recaps.index',
                'tone' => 'indigo',
                'help' => 'Récaps en attente de validation.',
                'icon' => 'check-circle',
            ],
            [
                'label' => 'Rapport église en retard',
                'value' => $rapportRetard,
                'route' => 'finances.rapports-mensuels.index',
                'tone' => 'rose',
                'help' => 'Rapport mensuel de la période non finalisé.',
                'icon' => 'document-chart-bar',
            ],
        ]);
    }

    /**
     * @return list<array{label:string,courant:float,precedent:float,suffix:string}>
     */
    private function comparatifMensuelPour(User $user): array
    {
        $now = now();
        $prevYear = $now->month > 1 ? (int) $now->year : (int) $now->year - 1;
        $prevMonth = $now->month > 1 ? (int) $now->month - 1 : 12;

        if ($user->mission_id !== null && $user->eglise_locale_id === null) {
            $missionId = (int) $user->mission_id;

            $current = MissionTresorerieRapportMensuel::query()
                ->where('mission_id', $missionId)
                ->where('annee', (int) $now->year)
                ->where('mois', (int) $now->month)
                ->selectRaw('COALESCE(SUM(dimes_eglises + autres_dimes),0) as dimes, COALESCE(SUM(offrandes_mois),0) as offrandes')
                ->first();

            $previous = MissionTresorerieRapportMensuel::query()
                ->where('mission_id', $missionId)
                ->where('annee', $prevYear)
                ->where('mois', $prevMonth)
                ->selectRaw('COALESCE(SUM(dimes_eglises + autres_dimes),0) as dimes, COALESCE(SUM(offrandes_mois),0) as offrandes')
                ->first();

            $curDimes = (float) ($current?->dimes ?? 0);
            $curOff = (float) ($current?->offrandes ?? 0);
            $prevDimes = (float) ($previous?->dimes ?? 0);
            $prevOff = (float) ($previous?->offrandes ?? 0);

            return [
                ['label' => 'Dîmes', 'courant' => $curDimes, 'precedent' => $prevDimes, 'suffix' => 'FCFA'],
                ['label' => 'Offrandes', 'courant' => $curOff, 'precedent' => $prevOff, 'suffix' => 'FCFA'],
                ['label' => 'Total recettes', 'courant' => $curDimes + $curOff, 'precedent' => $prevDimes + $prevOff, 'suffix' => 'FCFA'],
            ];
        }

        $current = $this->rapportsScopes($user)
            ->where('annee', (int) $now->year)
            ->where('mois', (int) $now->month)
            ->selectRaw('COALESCE(SUM(total_dimes_mois),0) as dimes, COALESCE(SUM(total_moitie_offrandes_mois + total_autres_offrandes_mission_mois),0) as offrandes, COALESCE(SUM(total_a_transferer_mission_mois),0) as transferer')
            ->first();

        $previous = $this->rapportsScopes($user)
            ->where('annee', $prevYear)
            ->where('mois', $prevMonth)
            ->selectRaw('COALESCE(SUM(total_dimes_mois),0) as dimes, COALESCE(SUM(total_moitie_offrandes_mois + total_autres_offrandes_mission_mois),0) as offrandes, COALESCE(SUM(total_a_transferer_mission_mois),0) as transferer')
            ->first();

        return [
            ['label' => 'Dîmes', 'courant' => (float) ($current?->dimes ?? 0), 'precedent' => (float) ($previous?->dimes ?? 0), 'suffix' => 'FCFA'],
            ['label' => 'Offrandes', 'courant' => (float) ($current?->offrandes ?? 0), 'precedent' => (float) ($previous?->offrandes ?? 0), 'suffix' => 'FCFA'],
            ['label' => 'À transférer mission', 'courant' => (float) ($current?->transferer ?? 0), 'precedent' => (float) ($previous?->transferer ?? 0), 'suffix' => 'FCFA'],
        ];
    }

    private function rapportsEglisesEnRetard(int $missionId, int $annee, int $mois): int
    {
        $eglisesActives = (int) EgliseLocale::query()
            ->where('mission_id', $missionId)
            ->where('actif', true)
            ->count();

        $rapportsOk = (int) RapportMensuelEglise::query()
            ->where('annee', $annee)
            ->where('mois', $mois)
            ->whereIn('etat_transmission', [
                RapportMensuelEglise::ETAT_SOUMIS,
                RapportMensuelEglise::ETAT_VALIDE_MISSION,
            ])
            ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId)->where('actif', true))
            ->distinct('eglise_locale_id')
            ->count('eglise_locale_id');

        return max(0, $eglisesActives - $rapportsOk);
    }

    private function transfertsNonRapproches(int $missionId, int $annee, int $moisMax): int
    {
        if (! Schema::hasTable('mission_tresorerie_transferts_bancaires')) {
            return 0;
        }

        $rapports = MissionTresorerieRapportMensuel::query()
            ->where('mission_id', $missionId)
            ->where('annee', $annee)
            ->where('mois', '<=', $moisMax)
            ->get(['mois', 'dimes_eglises', 'autres_dimes', 'offrandes_mois'])
            ->groupBy('mois');

        $transferts = MissionTresorerieTransfertBancaire::query()
            ->where('mission_id', $missionId)
            ->where('annee', $annee)
            ->where('mois', '<=', $moisMax)
            ->pluck('montant_transfere', 'mois');

        $count = 0;
        for ($m = 1; $m <= $moisMax; $m++) {
            $rows = $rapports->get($m, collect());
            $dimes = (float) $rows->sum(fn ($r) => (float) $r->dimes_eglises + (float) $r->autres_dimes);
            $offrandes = (float) $rows->sum(fn ($r) => (float) $r->offrandes_mois);
            $attendu = round(($dimes * 0.31) + ($offrandes * 0.30), 2);
            $effectue = round((float) ($transferts[$m] ?? 0), 2);

            if (abs($attendu - $effectue) > 0.009) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  list<array{label:string,value:int,route:string,tone:string,help:string,icon:string}>  $priorites
     * @return list<array{label:string,value:int,route:string,tone:string,help:string,icon:string}>
     */
    private function trierPriorites(array $priorites): array
    {
        $weights = [
            'rose' => 30,
            'amber' => 20,
            'indigo' => 10,
            'slate' => 0,
        ];

        usort($priorites, function (array $a, array $b) use ($weights): int {
            $scoreA = ($weights[$a['tone']] ?? 0) + min((int) $a['value'], 999);
            $scoreB = ($weights[$b['tone']] ?? 0) + min((int) $b['value'], 999);

            return $scoreB <=> $scoreA;
        });

        return $priorites;
    }
}
