@extends('layouts.app')

@section('page-title', 'Mon profil')

@section('page-title-info')
{{ auth()->user()->name }}
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Mon profil</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Gérez vos informations personnelles et vos préférences.
            </p>
        </div>

        <div class="p-6">
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Informations personnelles -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-4">Informations personnelles</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Nom complet</dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Email</dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $user->email }}</dd>
                            </div>
                            @if($user->eglise_locale_id)
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Église locale</dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $user->egliseLocale?->nom ?? '—' }}</dd>
                            </div>
                            @endif
                            @if($user->mission_id)
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Mission</dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $user->mission?->nom ?? '—' }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Rôles et permissions -->
                    <div>
                        <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-4">Rôles et permissions</h3>
                        <div class="space-y-2">
                            @foreach($user->roles as $role)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                                {{ $role->nom }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Statistiques d'activité -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-4">Activité récente</h3>
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->created_at->format('d/m/Y') }}</div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">Membre depuis</div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div>
                        <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-4">Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('password.request') }}" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Changer le mot de passe
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
