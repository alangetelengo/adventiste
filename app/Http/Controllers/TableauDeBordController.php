<?php

namespace App\Http\Controllers;

use App\Models\Bapteme;
use App\Models\District;
use App\Models\EgliseLocale;
use App\Models\GroupeMission;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\Membre;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\MissionTresorerieTransfertBancaire;
use App\Models\Permission;
use App\Models\RapportMembreEglise;
use App\Models\RapportMensuelEglise;
use App\Models\RapportStationMission;
use App\Models\RecapSabbatEglise;
use App\Models\Role;
use App\Models\User;
use App\Support\NavigationGate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TableauDeBordController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $stats = $this->ajusterStatsHeroPourRole($user, $this->statsPour($user));
        $roleStats = $this->filtrerRoleStatsPourUtilisateur($user, $this->statsParRole($user));
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

    /**
     * @param  array{recaps_total: int, recaps_brouillon: int, recaps_ce_mois: int, rapports_total: int, rapports_non_verrouilles: int, eglises_actives: int|null, membres_total: int}  $stats
     * @return array{recaps_total: int, recaps_brouillon: int, recaps_ce_mois: int, rapports_total: int, rapports_non_verrouilles: int, eglises_actives: int|null, membres_total: int}
     */
    private function ajusterStatsHeroPourRole(User $user, array $stats): array
    {
        if ($user->hasRole('secretaire_eglise') || $user->hasRole('secretaire_executif_mission')) {
            $stats['recaps_total'] = 0;
            $stats['recaps_brouillon'] = 0;
            $stats['recaps_ce_mois'] = 0;
        }

        return $stats;
    }

    private function membresScopes(User $user): Builder
    {
        $q = Membre::query();

        if ($user->eglise_locale_id !== null) {
            $q->where('eglise_locale_id', $user->eglise_locale_id);
        } elseif ($user->mission_id !== null) {
            $q->whereHas('egliseLocale', fn ($q2) => $q2->where('mission_id', $user->mission_id));
        } else {
            $q->whereRaw('1 = 0');
        }

        return $q;
    }

    private function recapsScopes(User $user): Builder
    {
        $q = RecapSabbatEglise::query();

        if ($user->eglise_locale_id !== null) {
            $q->where('eglise_locale_id', $user->eglise_locale_id);
        } elseif ($user->mission_id !== null) {
            $q->whereHas('egliseLocale', fn ($q2) => $q2->where('mission_id', $user->mission_id));
        } else {
            $q->whereRaw('1 = 0');
        }

        return $q;
    }

    private function rapportsScopes(User $user): Builder
    {
        $q = RapportMensuelEglise::query();

        if ($user->eglise_locale_id !== null) {
            $q->where('eglise_locale_id', $user->eglise_locale_id);
        } elseif ($user->mission_id !== null) {
            $q->whereHas('egliseLocale', fn ($q2) => $q2->where('mission_id', $user->mission_id));
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
            'role_label' => __('dashboard.roles.secretaire_eglise'),
            'cards' => [
                [
                    'title' => __('dashboard.cards.secretaire_eglise.membres.title'),
                    'description' => __('dashboard.cards.secretaire_eglise.membres.description'),
                    'icon' => 'users',
                    'route' => 'membres.index',
                    'stats' => [
                        'total' => $this->membresScopes($user)->count(),
                        'actifs' => $this->membresScopes($user)->where('actif', true)->count(),
                    ],
                    'color' => 'blue',
                ],
                [
                    'title' => __('dashboard.cards.secretaire_eglise.rapports_membres.title'),
                    'description' => __('dashboard.cards.secretaire_eglise.rapports_membres.description'),
                    'icon' => 'clipboard-list',
                    'route' => 'secretariat.rapports-membres.index',
                    'stats' => [
                        'soumis' => RapportMembreEglise::query()
                            ->where('eglise_locale_id', $user->eglise_locale_id)
                            ->where('etat', RapportMembreEglise::ETAT_SOUMIS)
                            ->count(),
                    ],
                    'color' => 'indigo',
                ],
                [
                    'title' => __('dashboard.cards.secretaire_eglise.rapports_mensuels.title'),
                    'description' => __('dashboard.cards.secretaire_eglise.rapports_mensuels.description'),
                    'icon' => 'chart-bar',
                    'route' => 'finances.rapports-mensuels.index',
                    'stats' => [
                        'total' => $this->rapportsScopes($user)->count(),
                        'soumis_mission' => $this->rapportsScopes($user)->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)->count(),
                    ],
                    'color' => 'purple',
                ],
                [
                    'title' => __('dashboard.cards.secretaire_eglise.baptemes.title'),
                    'description' => __('dashboard.cards.secretaire_eglise.baptemes.description'),
                    'icon' => 'document',
                    'route' => 'baptemes.index',
                    'stats' => [
                        'ce_mois' => Bapteme::query()
                            ->where('eglise_locale_id', $user->eglise_locale_id)
                            ->whereYear('date_bapteme', now()->year)
                            ->whereMonth('date_bapteme', now()->month)
                            ->count(),
                    ],
                    'color' => 'emerald',
                ],
            ],
            'actions' => [
                ['label' => __('dashboard.actions.nouveau_membre'), 'route' => 'membres.create', 'icon' => 'plus'],
                ['label' => __('dashboard.actions.rapports_mensuels'), 'route' => 'finances.rapports-mensuels.index', 'icon' => 'eye'],
                ['label' => __('dashboard.actions.rapports_membres'), 'route' => 'secretariat.rapports-membres.index', 'icon' => 'document-chart-bar'],
            ],
        ];
    }

    private function statsTresorierEglise(User $user): array
    {
        return [
            'role_name' => 'tresorier_eglise',
            'role_label' => __('dashboard.roles.tresorier_eglise'),
            'cards' => [
                [
                    'title' => __('dashboard.cards.tresorier_eglise.recaps.title'),
                    'description' => __('dashboard.cards.tresorier_eglise.recaps.description'),
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
                    'title' => __('dashboard.cards.tresorier_eglise.rapports_mensuels.title'),
                    'description' => __('dashboard.cards.tresorier_eglise.rapports_mensuels.description'),
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
                ['label' => __('dashboard.actions.nouveau_recap'), 'route' => 'finances.recaps.create', 'icon' => 'plus'],
                ['label' => __('dashboard.actions.voir_rapports'), 'route' => 'finances.rapports-mensuels.index', 'icon' => 'eye'],
            ],
        ];
    }

    private function statsSecretaireMission(User $user): array
    {
        $missionId = (int) $user->mission_id;
        $qRm = RapportMensuelEglise::query()->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId));

        return [
            'role_name' => 'secretaire_executif_mission',
            'role_label' => __('dashboard.roles.secretaire_executif_mission'),
            'cards' => [
                [
                    'title' => __('dashboard.cards.secretaire_executif_mission.vue.title'),
                    'description' => __('dashboard.cards.secretaire_executif_mission.vue.description'),
                    'icon' => 'building',
                    'route' => 'tableau-de-bord',
                    'stats' => [
                        'eglises' => EgliseLocale::where('mission_id', $missionId)->where('actif', true)->count(),
                        'membres_total' => $this->membresScopes($user)->count(),
                    ],
                    'color' => 'indigo',
                ],
                [
                    'title' => __('dashboard.cards.secretaire_executif_mission.rapports_mensuels.title'),
                    'description' => __('dashboard.cards.secretaire_executif_mission.rapports_mensuels.description'),
                    'icon' => 'chart-bar',
                    'route' => 'finances.rapports-mensuels.index',
                    'stats' => [
                        'total' => (clone $qRm)->count(),
                        'soumis_mission' => (clone $qRm)->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)->count(),
                    ],
                    'color' => 'purple',
                ],
                [
                    'title' => __('dashboard.cards.secretaire_executif_mission.rapports_membres.title'),
                    'description' => __('dashboard.cards.secretaire_executif_mission.rapports_membres.description'),
                    'icon' => 'clipboard-list',
                    'route' => 'secretariat.rapports-membres.index',
                    'stats' => [
                        'soumis' => RapportMembreEglise::query()
                            ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
                            ->where('etat', RapportMembreEglise::ETAT_SOUMIS)
                            ->count(),
                    ],
                    'color' => 'blue',
                ],
            ],
            'actions' => [
                ['label' => __('dashboard.actions.gerer_eglises'), 'route' => 'parametres.eglises.index', 'icon' => 'building'],
                ['label' => __('dashboard.actions.gerer_membres'), 'route' => 'membres.index', 'icon' => 'users'],
                ['label' => __('dashboard.actions.rapports_mensuels'), 'route' => 'finances.rapports-mensuels.index', 'icon' => 'eye'],
                ['label' => __('dashboard.actions.rapports_membres'), 'route' => 'secretariat.rapports-membres.index', 'icon' => 'clipboard-list'],
            ],
        ];
    }

    private function statsTresorierMission(User $user): array
    {
        return [
            'role_name' => 'tresorier_mission',
            'role_label' => __('dashboard.roles.tresorier_mission'),
            'cards' => [
                [
                    'title' => __('dashboard.cards.tresorier_mission.finances.title'),
                    'description' => __('dashboard.cards.tresorier_mission.finances.description'),
                    'icon' => 'banknotes',
                    'route' => 'finances.rapports-station.index',
                    'stats' => [
                        'rapports_total' => RapportStationMission::where('mission_id', $user->mission_id)->count(),
                        'rapports_mois' => RapportStationMission::where('mission_id', $user->mission_id)->where('annee', now()->year)->where('mois', now()->month)->count(),
                    ],
                    'color' => 'emerald',
                ],
                [
                    'title' => __('dashboard.cards.tresorier_mission.validation_recaps.title'),
                    'description' => __('dashboard.cards.tresorier_mission.validation_recaps.description'),
                    'icon' => 'check-circle',
                    'route' => 'finances.recaps.index',
                    'stats' => [
                        'a_valider' => $this->recapsScopes($user)->where('statut', 'soumis')->count(),
                        'verrouilles' => $this->recapsScopes($user)->where('statut', 'verrouille')->count(),
                    ],
                    'color' => 'blue',
                ],
                [
                    'title' => __('dashboard.cards.tresorier_mission.rapports_mensuels.title'),
                    'description' => __('dashboard.cards.tresorier_mission.rapports_mensuels.description'),
                    'icon' => 'chart-bar',
                    'route' => 'finances.rapports-mensuels.index',
                    'stats' => [
                        'total' => RapportMensuelEglise::query()
                            ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $user->mission_id))
                            ->count(),
                        'a_valider' => RapportMensuelEglise::query()
                            ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $user->mission_id))
                            ->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)
                            ->count(),
                    ],
                    'color' => 'purple',
                ],
                [
                    'title' => __('dashboard.cards.tresorier_mission.groupes.title'),
                    'description' => __('dashboard.cards.tresorier_mission.groupes.description'),
                    'icon' => 'users',
                    'route' => 'parametres.groupes-mission.index',
                    'stats' => [
                        'total' => GroupeMission::where('mission_id', $user->mission_id)->count(),
                        'actifs' => GroupeMission::where('mission_id', $user->mission_id)->where('actif', true)->count(),
                    ],
                    'color' => 'orange',
                ],
            ],
            'actions' => [
                ['label' => __('dashboard.actions.nouveau_rapport_station'), 'route' => 'finances.rapports-station.create', 'icon' => 'plus'],
                ['label' => __('dashboard.actions.rapports_mensuels'), 'route' => 'finances.rapports-mensuels.index', 'icon' => 'eye'],
                ['label' => __('dashboard.actions.gerer_groupes'), 'route' => 'parametres.groupes-mission.index', 'icon' => 'users'],
                ['label' => __('dashboard.actions.types_recette'), 'route' => 'parametres.types-recette.index', 'icon' => 'document'],
            ],
        ];
    }

    private function statsPresidentMission(User $user): array
    {
        return [
            'role_name' => 'president_mission',
            'role_label' => __('dashboard.roles.president_mission'),
            'cards' => [
                [
                    'title' => __('dashboard.cards.president_mission.strategie.title'),
                    'description' => __('dashboard.cards.president_mission.strategie.description'),
                    'icon' => 'presentation-chart-line',
                    'route' => 'tableau-de-bord',
                    'stats' => [
                        'eglises_actives' => EgliseLocale::where('mission_id', $user->mission_id)->where('actif', true)->count(),
                        'membres_total' => $this->membresScopes($user)->count(),
                        'districts' => District::where('mission_id', $user->mission_id)->count(),
                    ],
                    'color' => 'slate',
                ],
                [
                    'title' => __('dashboard.cards.president_mission.finances.title'),
                    'description' => __('dashboard.cards.president_mission.finances.description'),
                    'icon' => 'currency-dollar',
                    'route' => 'finances.rapports-station.index',
                    'stats' => [
                        'rapports_ce_mois' => RapportStationMission::where('mission_id', $user->mission_id)->where('annee', now()->year)->where('mois', now()->month)->count(),
                        'recaps_ce_mois' => $this->recapsScopes($user)->whereYear('date_sabbat', now()->year)->whereMonth('date_sabbat', now()->month)->count(),
                    ],
                    'color' => 'green',
                ],
                [
                    'title' => __('dashboard.cards.president_mission.admin.title'),
                    'description' => __('dashboard.cards.president_mission.admin.description'),
                    'icon' => 'cog-6-tooth',
                    'route' => 'parametres.utilisateurs.index',
                    'stats' => [
                        'utilisateurs' => User::where('mission_id', $user->mission_id)->count(),
                        'roles_actifs' => Role::whereHas('users', fn ($q) => $q->where('mission_id', $user->mission_id))->count(),
                    ],
                    'color' => 'gray',
                ],
            ],
            'actions' => [
                ['label' => __('dashboard.actions.gerer_utilisateurs'), 'route' => 'parametres.utilisateurs.index', 'icon' => 'users'],
                ['label' => __('dashboard.actions.parametres_mission'), 'route' => 'parametres.index', 'icon' => 'cog'],
                ['label' => __('dashboard.actions.rapports_mensuels_eglises'), 'route' => 'finances.rapports-mensuels.index', 'icon' => 'eye'],
                ['label' => __('dashboard.actions.rapports_station'), 'route' => 'finances.rapports-station.index', 'icon' => 'chart-bar'],
            ],
        ];
    }

    private function statsAdminMission(User $user): array
    {
        return [
            'role_name' => 'admin_mission',
            'role_label' => __('dashboard.roles.admin_mission'),
            'cards' => [
                [
                    'title' => __('dashboard.cards.admin_mission.admin_complete.title'),
                    'description' => __('dashboard.cards.admin_mission.admin_complete.description'),
                    'icon' => 'shield-check',
                    'route' => 'parametres.index',
                    'stats' => [
                        'utilisateurs_total' => User::where('mission_id', $user->mission_id)->count(),
                        'permissions_total' => Permission::count(),
                        'roles_total' => Role::count(),
                    ],
                    'color' => 'red',
                ],
                [
                    'title' => __('dashboard.cards.admin_mission.donnees_fin.title'),
                    'description' => __('dashboard.cards.admin_mission.donnees_fin.description'),
                    'icon' => 'banknotes',
                    'route' => 'finances.rapports-station.index',
                    'stats' => [
                        'tous_recaps' => $this->recapsScopes($user)->count(),
                        'tous_rapports' => RapportStationMission::where('mission_id', $user->mission_id)->count(),
                    ],
                    'color' => 'emerald',
                ],
                [
                    'title' => __('dashboard.cards.admin_mission.structure.title'),
                    'description' => __('dashboard.cards.admin_mission.structure.description'),
                    'icon' => 'building',
                    'route' => 'parametres.eglises.index',
                    'stats' => [
                        'eglises_total' => EgliseLocale::where('mission_id', $user->mission_id)->count(),
                        'districts_total' => District::where('mission_id', $user->mission_id)->count(),
                        'groupes_total' => GroupeMission::where('mission_id', $user->mission_id)->count(),
                    ],
                    'color' => 'blue',
                ],
            ],
            'actions' => [
                ['label' => __('dashboard.actions.administration'), 'route' => 'parametres.index', 'icon' => 'cog'],
                ['label' => __('dashboard.actions.gerer_utilisateurs'), 'route' => 'parametres.utilisateurs.index', 'icon' => 'users'],
                ['label' => __('dashboard.actions.rapports_mensuels_eglises'), 'route' => 'finances.rapports-mensuels.index', 'icon' => 'eye'],
                ['label' => __('dashboard.actions.permissions'), 'route' => 'parametres.permissions.index', 'icon' => 'document'],
                ['label' => __('dashboard.actions.roles'), 'route' => 'parametres.roles.index', 'icon' => 'users'],
            ],
        ];
    }

    private function statsDefault(User $user): array
    {
        return [
            'role_name' => 'default',
            'role_label' => __('dashboard.roles.default'),
            'cards' => [
                [
                    'title' => __('dashboard.cards.default.mes_donnees.title'),
                    'description' => __('dashboard.cards.default.mes_donnees.description'),
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
     *   recapsRecents:Collection<int,RecapSabbatEglise>,
     *   rapportsSoumis:Collection<int,RapportMensuelEglise>
     * }
     */
    private function dashboardV2Data(User $user): array
    {
        $dashboard = match ($user->role?->name) {
            'secretaire_eglise' => $this->dashboardSecretaireEglise($user),
            'tresorier_eglise' => $this->dashboardTresorierEglise($user),
            'secretaire_executif_mission' => $this->dashboardMissionConsolidee($user, 'secretaire_executif_mission'),
            'president_mission' => $this->dashboardMissionConsolidee($user, 'president_mission'),
            default => $this->dashboardParDefaut($user),
        };

        $dashboard['priorites'] = array_values(array_filter(
            $this->prioritesPour($user),
            fn (array $p) => empty($p['route']) || NavigationGate::canVisitRoute($user, $p['route']),
        ));
        $dashboard['comparatifMensuel'] = $this->peutVoirComparatifMensuel($user)
            ? $this->comparatifMensuelPour($user)
            : [];

        return $dashboard;
    }

    /**
     * @param  array{role_name: string, role_label: string, cards: array, actions: array}  $roleStats
     * @return array{role_name: string, role_label: string, cards: array, actions: array}
     */
    private function filtrerRoleStatsPourUtilisateur(User $user, array $roleStats): array
    {
        $roleStats['cards'] = array_values(array_filter(
            $roleStats['cards'] ?? [],
            fn (array $c) => empty($c['route']) || NavigationGate::canVisitRoute($user, $c['route']),
        ));
        $roleStats['actions'] = array_values(array_filter(
            $roleStats['actions'] ?? [],
            fn (array $a) => empty($a['route']) || NavigationGate::canVisitRoute($user, $a['route']),
        ));

        return $roleStats;
    }

    private function peutVoirComparatifMensuel(User $user): bool
    {
        if (in_array($user->role?->name, ['secretaire_eglise', 'secretaire_executif_mission'], true)) {
            return false;
        }

        $g = Gate::forUser($user);

        if ($user->mission_id !== null && $user->eglise_locale_id === null) {
            return $g->allows('viewAny', MissionTresorerieRapportMensuel::class);
        }

        return $g->allows('viewAny', RapportMensuelEglise::class);
    }

    /**
     * @return array{
     *   role:string,
     *   periode:string,
     *   kpis:list<array{label:string,value:int|float,suffix:string}>,
     *   recapsRecents:Collection<int,RecapSabbatEglise>,
     *   rapportsSoumis:Collection<int,RapportMensuelEglise>
     * }
     */
    private function dashboardTresorierEglise(User $user): array
    {
        $now = now();
        $g = Gate::forUser($user);
        $canRecap = $g->allows('viewAny', RecapSabbatEglise::class);
        $canRapport = $g->allows('viewAny', RapportMensuelEglise::class);

        $recapBase = $this->recapsScopes($user);
        $rapportBase = $this->rapportsScopes($user);

        $recapsMois = (clone $recapBase)
            ->whereYear('date_sabbat', $now->year)
            ->whereMonth('date_sabbat', $now->month);

        $recapIdsMois = (clone $recapsMois)->pluck('id');
        $recettesMois = 0.0;
        if ($canRecap && $recapIdsMois->isNotEmpty()) {
            $recettesMois = (float) LigneDimeOffrandeRecap::query()
                ->whereIn('recap_sabbat_eglise_id', $recapIdsMois)
                ->selectRaw('COALESCE(SUM(dimes + offrandes),0) as total')
                ->value('total');
        }

        $rapportMois = (clone $rapportBase)
            ->where('annee', $now->year)
            ->where('mois', $now->month);

        $kpisList = [];
        if ($canRecap) {
            $kpisList[] = ['label' => __('dashboard.kpi.recettes_mois'), 'value' => (int) round($recettesMois), 'suffix' => 'FCFA'];
            $kpisList[] = ['label' => __('dashboard.kpi.recaps_saisis'), 'value' => (int) (clone $recapsMois)->count(), 'suffix' => ''];
        }
        if ($canRapport) {
            $kpisList[] = ['label' => __('dashboard.kpi.a_transferer_mission'), 'value' => (int) round((float) (clone $rapportMois)->sum('total_a_transferer_mission_mois')), 'suffix' => 'FCFA'];
            $kpisList[] = ['label' => __('dashboard.kpi.rapports_soumis'), 'value' => (int) (clone $rapportBase)->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)->count(), 'suffix' => ''];
        }

        $recapsRecents = $canRecap
            ? (clone $recapBase)
                ->with('egliseLocale')
                ->withSum('lignesContributions as total_dimes', 'dimes')
                ->withSum('lignesContributions as total_offrandes', 'offrandes')
                ->latest('date_sabbat')
                ->limit(6)
                ->get()
            : collect();

        $rapportsSoumis = $canRapport
            ? (clone $rapportBase)
                ->with('egliseLocale')
                ->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)
                ->orderByDesc('soumis_le')
                ->limit(6)
                ->get()
            : collect();

        return [
            'role' => 'tresorier_eglise',
            'periode' => $now->translatedFormat('F Y'),
            'kpis' => $kpisList,
            'recapsRecents' => $recapsRecents,
            'rapportsSoumis' => $rapportsSoumis,
        ];
    }

    /**
     * @return array{
     *   role:string,
     *   periode:string,
     *   kpis:list<array{label:string,value:int|float,suffix:string}>,
     *   rapportsMembresRecents:Collection<int,RapportMembreEglise>,
     *   baptemesRecents:Collection<int,Bapteme>
     * }
     */
    private function dashboardSecretaireEglise(User $user): array
    {
        $now = now();
        $g = Gate::forUser($user);
        $canMembre = $g->allows('viewAny', Membre::class);
        $canBapteme = $g->allows('viewAny', Bapteme::class);
        $canRapportMembre = $g->allows('viewAny', RapportMembreEglise::class);

        $membresBase = $this->membresScopes($user);
        $egliseId = (int) ($user->eglise_locale_id ?? 0);

        $rapportsMembresRecents = $canRapportMembre
            ? RapportMembreEglise::query()
                ->where('eglise_locale_id', $egliseId)
                ->orderByDesc('created_at')
                ->limit(6)
                ->get()
            : collect();

        $baptemesRecents = $canBapteme
            ? Bapteme::query()
                ->where('eglise_locale_id', $egliseId)
                ->orderByDesc('date_bapteme')
                ->limit(6)
                ->get()
            : collect();

        $kpisList = [];
        if ($canMembre) {
            $kpisList[] = ['label' => __('dashboard.kpi.membres_total'), 'value' => (int) (clone $membresBase)->count(), 'suffix' => ''];
            $kpisList[] = ['label' => __('dashboard.kpi.membres_actifs'), 'value' => (int) (clone $membresBase)->where('actif', true)->count(), 'suffix' => ''];
        }
        if ($canBapteme) {
            $kpisList[] = ['label' => __('dashboard.kpi.baptemes_mois'), 'value' => (int) Bapteme::query()->where('eglise_locale_id', $egliseId)->whereYear('date_bapteme', $now->year)->whereMonth('date_bapteme', $now->month)->count(), 'suffix' => ''];
        }
        if ($canRapportMembre) {
            $kpisList[] = ['label' => __('dashboard.kpi.rapports_membres_soumis'), 'value' => (int) RapportMembreEglise::query()->where('eglise_locale_id', $egliseId)->where('etat', RapportMembreEglise::ETAT_SOUMIS)->count(), 'suffix' => ''];
        }

        return [
            'role' => 'secretaire_eglise',
            'periode' => $now->translatedFormat('F Y'),
            'kpis' => $kpisList,
            'rapportsMembresRecents' => $rapportsMembresRecents,
            'baptemesRecents' => $baptemesRecents,
        ];
    }

    /**
     * @return array{
     *   role:string,
     *   periode:string,
     *   kpis:list<array{label:string,value:int|float,suffix:string}>,
     *   rapportsFinancesMission:Collection<int,RapportMensuelEglise>,
     *   rapportsMembresMission:Collection<int,RapportMembreEglise>
     * }
     */
    /**
     * @param  'president_mission'|'secretaire_executif_mission'  $dashboardRole
     * @return array{
     *   role:string,
     *   periode:string,
     *   kpis:list<array{label:string,value:int|float,suffix:string}>,
     *   rapportsFinancesMission:Collection<int,RapportMensuelEglise>,
     *   rapportsMembresMission:Collection<int,RapportMembreEglise>
     * }
     */
    private function dashboardMissionConsolidee(User $user, string $dashboardRole): array
    {
        $now = now();
        $missionId = (int) ($user->mission_id ?? 0);
        $g = Gate::forUser($user);
        $canEglise = $g->allows('viewAny', EgliseLocale::class);
        $canMembre = $g->allows('viewAny', Membre::class);
        $canRapportFin = $g->allows('viewAny', RapportMensuelEglise::class);
        $canRapportMembre = $g->allows('viewAny', RapportMembreEglise::class);

        $rapportsFinancesMission = $canRapportFin
            ? RapportMensuelEglise::query()
                ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
                ->with('egliseLocale')
                ->orderByDesc('soumis_le')
                ->limit(8)
                ->get()
            : collect();

        $rapportsMembresMission = $canRapportMembre
            ? RapportMembreEglise::query()
                ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
                ->with('egliseLocale')
                ->orderByDesc('soumis_le')
                ->limit(8)
                ->get()
            : collect();

        $kpisList = [];
        if ($canEglise) {
            $kpisList[] = ['label' => __('dashboard.kpi.eglises_actives'), 'value' => (int) EgliseLocale::query()->where('mission_id', $missionId)->where('actif', true)->count(), 'suffix' => ''];
        }
        if ($canMembre) {
            $kpisList[] = ['label' => __('dashboard.kpi.membres_mission'), 'value' => (int) $this->membresScopes($user)->count(), 'suffix' => ''];
        }
        if ($canRapportFin) {
            $labelFinSoumis = $dashboardRole === 'secretaire_executif_mission'
                ? __('dashboard.kpi.rapports_mensuels_soumis')
                : __('dashboard.kpi.rapports_finances_soumis');
            $kpisList[] = ['label' => $labelFinSoumis, 'value' => (int) RapportMensuelEglise::query()->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)->count(), 'suffix' => ''];
        }
        if ($canRapportMembre) {
            $kpisList[] = ['label' => __('dashboard.kpi.rapports_membres_soumis'), 'value' => (int) RapportMembreEglise::query()->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))->where('etat', RapportMembreEglise::ETAT_SOUMIS)->count(), 'suffix' => ''];
        }

        return [
            'role' => $dashboardRole,
            'periode' => $now->translatedFormat('F Y'),
            'kpis' => $kpisList,
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
        $g = Gate::forUser($user);
        $kpisList = [];
        if ($g->allows('viewAny', RapportMensuelEglise::class)) {
            $kpisList[] = ['label' => __('dashboard.kpi.rapports_total'), 'value' => (int) $this->rapportsScopes($user)->count(), 'suffix' => ''];
        }
        if ($g->allows('viewAny', Membre::class)) {
            $kpisList[] = ['label' => __('dashboard.kpi.membres_total'), 'value' => (int) $this->membresScopes($user)->count(), 'suffix' => ''];
        }

        return [
            'role' => 'default',
            'periode' => now()->translatedFormat('F Y'),
            'kpis' => $kpisList,
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
            $prioritesMission = [];
            $prioritesTresoMission = $user->hasRole('tresorier_mission')
                || $user->hasRole('president_mission')
                || $user->hasRole('admin_mission');

            if ($prioritesTresoMission) {
                $prioritesMission[] = [
                    'label' => __('dashboard.priorite.transferts_non_rapproches'),
                    'value' => $this->transfertsNonRapproches($missionId, (int) $now->year, (int) $now->month),
                    'route' => 'finances.synthese-annuelle-mission.index',
                    'tone' => 'amber',
                    'help' => __('dashboard.priorite.transferts_help'),
                    'icon' => 'banknotes',
                ];
            }

            $prioritesMission[] = [
                'label' => __('dashboard.priorite.rapports_retard'),
                'value' => $this->rapportsEglisesEnRetard($missionId, (int) $now->year, (int) $now->month),
                'route' => 'finances.rapports-mensuels.index',
                'tone' => 'rose',
                'help' => __('dashboard.priorite.rapports_retard_help'),
                'icon' => 'document-chart-bar',
            ];

            if ($prioritesTresoMission) {
                $prioritesMission[] = [
                    'label' => __('dashboard.priorite.a_valider'),
                    'value' => (int) RapportMensuelEglise::query()
                        ->where('annee', (int) $now->year)
                        ->where('mois', (int) $now->month)
                        ->where('etat_transmission', RapportMensuelEglise::ETAT_SOUMIS)
                        ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
                        ->count(),
                    'route' => 'finances.rapports-mensuels.index',
                    'tone' => 'indigo',
                    'help' => __('dashboard.priorite.a_valider_help'),
                    'icon' => 'check-circle',
                ];
            }

            return $this->trierPriorites($prioritesMission);
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

        if ($user->hasRole('secretaire_eglise')) {
            return $this->trierPriorites([
                [
                    'label' => __('dashboard.priorite.rapport_mensuel_treso'),
                    'value' => $rapportRetard,
                    'route' => 'finances.rapports-mensuels.index',
                    'tone' => 'rose',
                    'help' => __('dashboard.priorite.rapport_mensuel_treso_help'),
                    'icon' => 'document-chart-bar',
                ],
            ]);
        }

        return $this->trierPriorites([
            [
                'label' => __('dashboard.priorite.recaps_brouillons'),
                'value' => (int) $this->recapsScopes($user)->where('statut', 'brouillon')->count(),
                'route' => 'finances.recaps.index',
                'tone' => 'amber',
                'help' => __('dashboard.priorite.recaps_brouillons_help'),
                'icon' => 'currency-dollar',
            ],
            [
                'label' => __('dashboard.priorite.recaps_soumis'),
                'value' => (int) $this->recapsScopes($user)->where('statut', 'soumis')->count(),
                'route' => 'finances.recaps.index',
                'tone' => 'indigo',
                'help' => __('dashboard.priorite.recaps_soumis_help'),
                'icon' => 'check-circle',
            ],
            [
                'label' => __('dashboard.priorite.rapport_retard_eglise'),
                'value' => $rapportRetard,
                'route' => 'finances.rapports-mensuels.index',
                'tone' => 'rose',
                'help' => __('dashboard.priorite.rapport_retard_eglise_help'),
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
                ['label' => __('dashboard.comparatif.dimes'), 'courant' => $curDimes, 'precedent' => $prevDimes, 'suffix' => 'FCFA'],
                ['label' => __('dashboard.comparatif.offrandes'), 'courant' => $curOff, 'precedent' => $prevOff, 'suffix' => 'FCFA'],
                ['label' => __('dashboard.comparatif.total_recettes'), 'courant' => $curDimes + $curOff, 'precedent' => $prevDimes + $prevOff, 'suffix' => 'FCFA'],
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
            ['label' => __('dashboard.comparatif.dimes'), 'courant' => (float) ($current?->dimes ?? 0), 'precedent' => (float) ($previous?->dimes ?? 0), 'suffix' => 'FCFA'],
            ['label' => __('dashboard.comparatif.offrandes'), 'courant' => (float) ($current?->offrandes ?? 0), 'precedent' => (float) ($previous?->offrandes ?? 0), 'suffix' => 'FCFA'],
            ['label' => __('dashboard.comparatif.a_transferer'), 'courant' => (float) ($current?->transferer ?? 0), 'precedent' => (float) ($previous?->transferer ?? 0), 'suffix' => 'FCFA'],
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
