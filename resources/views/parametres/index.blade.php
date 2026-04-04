@extends('layouts.app')

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('page-title', 'Paramètres')

@section('page-title-info')
    <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-400">
        <span class="hidden sm:inline h-px w-8 bg-linear-to-r from-transparent to-[#00b464]/45 dark:to-emerald-400/40"></span>
        Configuration de la mission et données de référence
    </span>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @can('viewAny', App\Models\EgliseLocale::class)
            <a href="{{ route('parametres.eglises.index') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Églises locales
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Annuaire des paroisses : codes uniques, rattachement district, indicateurs financiers de référence.
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Accéder au module
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @else
            <div class="rounded-xl border border-amber-200/80 dark:border-amber-800/50 bg-amber-50/50 dark:bg-amber-950/25 shadow-sm p-6 sm:p-7">
                <div class="flex gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-slate-900 dark:text-white">Accès restreint</h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            La gestion des églises est réservée aux comptes <strong class="text-slate-800 dark:text-slate-200">mission</strong> (sans rattachement à une église locale). Contactez votre administrateur.
                        </p>
                    </div>
                </div>
            </div>
        @endcan

        @can('viewAny', App\Models\District::class)
            <a href="{{ route('parametres.districts.index') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Districts
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Découpage territorial : rattachez chaque église locale à un district.
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Accéder au module
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @endcan

        @can('viewAny', App\Models\GroupeMission::class)
            <a href="{{ route('parametres.groupes-mission.index') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Groupes mission
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Petits groupes et services au niveau mission : codes uniques, rattachement des membres et ventilation financière.
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Accéder au module
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @endcan

        @can('viewAny', App\Models\MissionTresorerieVentilationLigne::class)
            <a href="{{ route('parametres.tresorerie-ventilation-lignes.edit') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10M4 18h10" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Lignes — ventilation trésorerie
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Ordre, libellés et pourcentages du rapport mensuel mission (remontée financière).
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Configurer
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @endcan

        @can('viewAny', App\Models\TypeRecetteMission::class)
            <a href="{{ route('parametres.types-recette.index') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Types de recette
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Dîme, offrandes cultuelles, construction, ÉDS, dons — liés à la ventilation (dîme / offrande / don).
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Gérer
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @endcan

        @can('viewAny', App\Models\TypeStatutMembre::class)
            <a href="{{ route('parametres.types-statut-membre.index') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A8.967 8.967 0 0012 21a8.967 8.967 0 006.879-3.196M15 11a3 3 0 11-6 0 3 3 0 016 0zM19.4 15a9 9 0 10-14.8 0" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Types de statut membre
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Catégories pastorales des membres (actif, régulier, irrégulier, sous censure, refroidi, etc.).
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Gérer
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @endcan

        @can('viewAny', App\Models\MissionReglesVentilationRecettes::class)
            <a href="{{ route('parametres.ventilation-recettes.edit') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Ventilation des recettes
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Pourcentages mission / église locale par type (dîme, offrande, don) pour les rapports mensuels et annuels.
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Configurer
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @endcan

        @can('viewAny', App\Models\User::class)
            <a href="{{ route('parametres.utilisateurs.index') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Utilisateurs
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Comptes de la mission : rôles, accès église pour les trésoriers.
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Accéder au module
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @else
            <div class="rounded-xl border-2 border-dashed border-slate-300/90 dark:border-slate-600 bg-slate-50/80 dark:bg-slate-800/40 shadow-sm p-6 sm:p-7">
                <div class="flex gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-dashed border-slate-300 dark:border-slate-600 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-slate-700 dark:text-slate-300">À venir</h2>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-500 leading-relaxed">
                            D’autres réglages avancés seront regroupés ici.
                        </p>
                    </div>
                </div>
            </div>
        @endcan

        @can('viewAny', App\Models\Role::class)
            <a href="{{ route('parametres.roles.index') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Rôles
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Rôles applicatifs et association aux permissions (RBAC).
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Accéder au module
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @endcan

        @can('viewAny', App\Models\Permission::class)
            <a href="{{ route('parametres.permissions.index') }}" class="adventiste-card-pro group block p-6 sm:p-7 text-left no-underline">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-[#00b464] ring-1 ring-emerald-200/60 dark:ring-emerald-800/50 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-[#00b464] dark:group-hover:text-emerald-300 transition-colors">
                            Permissions
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Granularité d’accès : créez et reliez les permissions aux rôles.
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#00b464] dark:text-emerald-400">
                            Accéder au module
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </p>
                    </div>
                </div>
            </a>
        @endcan
    </div>
@endsection
