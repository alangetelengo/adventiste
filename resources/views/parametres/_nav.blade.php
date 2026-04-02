<nav class="adventiste-pill-nav flex flex-wrap gap-2 mb-8 sm:mb-10" aria-label="Sous-menu paramètres">
    <a href="{{ route('parametres.index') }}" class="{{ request()->routeIs('parametres.index') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
        Vue d’ensemble
    </a>
    @can('viewAny', App\Models\District::class)
    <a href="{{ route('parametres.districts.index') }}" class="{{ request()->routeIs('parametres.districts.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
        </svg>
        Districts
    </a>
    @endcan
    @can('viewAny', App\Models\GroupeMission::class)
    <a href="{{ route('parametres.groupes-mission.index') }}" class="{{ request()->routeIs('parametres.groupes-mission.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Groupes mission
    </a>
    @endcan
    @can('viewAny', App\Models\MissionTresorerieVentilationLigne::class)
    <a href="{{ route('parametres.tresorerie-ventilation-lignes.edit') }}" class="{{ request()->routeIs('parametres.tresorerie-ventilation-lignes.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h10" />
        </svg>
        Ventilation trésorerie (lignes)
    </a>
    @endcan
    @can('viewAny', App\Models\TypeRecetteMission::class)
    <a href="{{ route('parametres.types-recette.index') }}" class="{{ request()->routeIs('parametres.types-recette.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Types de recette
    </a>
    @endcan
    @can('viewAny', App\Models\TypeStatutMembre::class)
    <a href="{{ route('parametres.types-statut-membre.index') }}" class="{{ request()->routeIs('parametres.types-statut-membre.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A8.967 8.967 0 0012 21a8.967 8.967 0 006.879-3.196M15 11a3 3 0 11-6 0 3 3 0 016 0zM19.4 15a9 9 0 10-14.8 0" />
        </svg>
        Types de statut membre
    </a>
    @endcan
    @can('viewAny', App\Models\MissionReglesVentilationRecettes::class)
    <a href="{{ route('parametres.ventilation-recettes.edit') }}" class="{{ request()->routeIs('parametres.ventilation-recettes.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        Ventilation recettes
    </a>
    @endcan
    @can('viewAny', App\Models\EgliseLocale::class)
    <a href="{{ route('parametres.eglises.index') }}" class="{{ request()->routeIs('parametres.eglises.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        Églises & Ministères
    </a>
    @endcan
    @can('viewAny', App\Models\User::class)
    <a href="{{ route('parametres.utilisateurs.index') }}" class="{{ request()->routeIs('parametres.utilisateurs.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        Utilisateurs
    </a>
    @endcan
    @can('viewAny', App\Models\Role::class)
    <a href="{{ route('parametres.roles.index') }}" class="{{ request()->routeIs('parametres.roles.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        Rôles
    </a>
    @endcan
    @can('viewAny', App\Models\Permission::class)
    <a href="{{ route('parametres.permissions.index') }}" class="{{ request()->routeIs('parametres.permissions.*') ? 'is-active' : '' }}">
        <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
        </svg>
        Permissions
    </a>
    @endcan
</nav>
