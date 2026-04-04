@extends('layouts.app')

@section('page-title', 'Mon profil')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ $user->name }}</span>
    <span class="text-slate-400 dark:text-slate-500"> · </span>
    <span class="text-slate-600 dark:text-slate-400">Consultez vos informations et gérez la sécurité de votre compte.</span>
@endsection

@section('btn-create')
    <a href="{{ route('tableau-de-bord') }}" class="adventiste-btn-secondary inline-flex items-center gap-2 no-underline">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Retour au tableau de bord
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <dl class="adventiste-card-pro-static p-6 sm:p-7 grid gap-5 sm:grid-cols-2 text-sm">
        <div class="sm:col-span-2">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4 pb-3 border-b border-slate-200/80 dark:border-slate-600/80">Informations personnelles</h2>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nom complet</dt>
            <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $user->name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Courriel</dt>
            <dd class="mt-1 text-slate-800 dark:text-slate-200 break-all">{{ $user->email }}</dd>
        </div>
        @if($user->eglise_locale_id)
        <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Église locale</dt>
            <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $user->egliseLocale?->nom ?? '—' }}</dd>
        </div>
        @endif
        @if($user->mission_id)
        <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Mission</dt>
            <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $user->mission?->nom ?? '—' }}</dd>
        </div>
        @endif
    </dl>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="adventiste-card-pro-static p-6 sm:p-7">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">Rôle dans l’application</h2>
            @if($user->role)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-900 dark:text-emerald-100 border border-emerald-200/80 dark:border-emerald-800/60">
                    {{ $user->role->label }}
                </span>
            @else
                <p class="text-sm text-slate-500 dark:text-slate-400">Aucun rôle assigné.</p>
            @endif
        </div>

        <div class="adventiste-card-pro-static p-6 sm:p-7 flex flex-col">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">Activité</h2>
            <div class="rounded-xl bg-slate-50/90 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-600/60 px-4 py-5 text-center grow flex flex-col justify-center">
                <p class="text-2xl font-bold tabular-nums text-slate-900 dark:text-white">{{ $user->created_at->format('d/m/Y') }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Membre depuis</p>
            </div>
        </div>
    </div>

    <div class="adventiste-card-pro-static p-6 sm:p-7">
        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">Sécurité du compte</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-5 max-w-2xl">
            Mettez à jour votre mot de passe régulièrement pour protéger l’accès à votre espace.
        </p>
        <a href="{{ route('profile.password.edit') }}" class="adventiste-btn-primary inline-flex items-center gap-2 no-underline w-full sm:w-auto justify-center">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            Changer le mot de passe
        </a>
    </div>
</div>
@endsection
