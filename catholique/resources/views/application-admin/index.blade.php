@extends('layouts.app')

@section('title', 'Administration — Catholique')
@section('page-title', 'Configuration de l\'application')
@section('page-title-info', 'Accédez à la structure (paroisses), à la gestion des comptes et des droits, aux référentiels financiers et aux paramètres d\'affichage, PDF et page de connexion.')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">

        @can('manage_paroisses')
            <a href="{{ route('paroisses.index') }}" class="group adventiste-card-pro-static p-5 no-underline block hover:border-emerald-500/40 transition-colors">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15 text-xl" aria-hidden="true">⛪</span>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-300">Paroisses</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 leading-snug">Créer et gérer les paroisses, curés et informations de structure.</p>
                    </div>
                </div>
            </a>
        @endcan

        @can('manage_users')
            <a href="{{ route('users.index') }}" class="group adventiste-card-pro-static p-5 no-underline block hover:border-emerald-500/40 transition-colors">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-500/15 text-xl" aria-hidden="true">👤</span>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-sky-700 dark:group-hover:text-sky-300">Utilisateurs</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 leading-snug">Comptes, rattachement à une paroisse et attribution des rôles.</p>
                    </div>
                </div>
            </a>
        @endcan

        @can('manage_roles')
            <a href="{{ route('roles.index') }}" class="group adventiste-card-pro-static p-5 no-underline block hover:border-emerald-500/40 transition-colors">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-500/15 text-xl" aria-hidden="true">🎭</span>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-violet-700 dark:group-hover:text-violet-300">Rôles</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 leading-snug">Définir les rôles et regrouper les permissions.</p>
                    </div>
                </div>
            </a>
        @endcan

        @can('manage_permissions')
            <a href="{{ route('permissions.index') }}" class="group adventiste-card-pro-static p-5 no-underline block hover:border-emerald-500/40 transition-colors">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-xl" aria-hidden="true">🔑</span>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-amber-800 dark:group-hover:text-amber-200">Permissions</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 leading-snug">Liste des droits fines (menus, actions) attachables aux rôles.</p>
                    </div>
                </div>
            </a>
        @endcan

        @can('view_revenues')
            <a href="{{ route('revenue-categories.index') }}" class="group adventiste-card-pro-static p-5 no-underline block hover:border-emerald-500/40 transition-colors">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-500/15 text-xl" aria-hidden="true">🏷️</span>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-rose-700 dark:group-hover:text-rose-300">Catégories de recettes</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 leading-snug">Référentiel des catégories (codes, ordre, actif).</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('revenue-types.index') }}" class="group adventiste-card-pro-static p-5 no-underline block hover:border-emerald-500/40 transition-colors">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-500/15 text-xl" aria-hidden="true">📋</span>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-orange-700 dark:group-hover:text-orange-300">Types de recettes</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 leading-snug">Référentiel des types liés aux saisies de recettes.</p>
                    </div>
                </div>
            </a>
        @endcan

        @can('view_configuration')
            <a href="{{ route('configurations.index') }}" class="group adventiste-card-pro-static p-5 no-underline block hover:border-emerald-500/40 transition-colors md:col-span-2 xl:col-span-1">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-500/15 text-xl" aria-hidden="true">🎨</span>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-slate-700 dark:group-hover:text-slate-200">Apparence, PDF &amp; connexion</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 leading-snug">Identité paroisse, couleurs, PDF, loader, image de fond de la page de connexion.</p>
                    </div>
                </div>
            </a>
        @endcan
    </div>

    <p class="mt-8 text-sm text-slate-500 dark:text-slate-400">
        Les cartes affichées correspondent à vos permissions. Le menu latéral « Administration » regroupe ces raccourcis.
    </p>
@endsection
