@extends('layouts.app')

@section('page-title', $eglise->nom)

@section('page-title-info')
<span class="inline-flex flex-wrap items-center gap-2 text-slate-600 dark:text-slate-400">
    <span class="font-mono text-xs sm:text-sm px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $eglise->code_unique }}</span>
    <span class="text-slate-300 dark:text-slate-600">·</span>
    <span>{{ $eglise->mission->nom }}</span>
</span>
@endsection

@section('content')
@include('parametres._nav')

<div class="max-w-3xl space-y-6">
    <div class="flex flex-wrap gap-3">
        @can('update', $eglise)
        <a href="{{ route('parametres.eglises.edit', $eglise) }}" class="adventiste-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            Modifier
        </a>
        @endcan
        <a href="{{ route('parametres.eglises.departements.index', $eglise) }}" class="adventiste-btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4" /></svg>
            Ministères & Départements
        </a>
        <a href="{{ route('parametres.eglises.index') }}" class="adventiste-btn-secondary">← Liste des églises</a>
    </div>

    <dl class="adventiste-card-pro-static p-6 sm:p-7 grid gap-5 sm:grid-cols-2 text-sm">
        <div class="sm:col-span-2 pb-4 border-b border-slate-200/80 dark:border-slate-600/50">
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Paroisse</dt>
            <dd class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">{{ $eglise->nom }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">District</dt>
            <dd class="mt-1.5 font-medium text-slate-800 dark:text-slate-100">{{ $eglise->district?->nom ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Statut</dt>
            <dd class="mt-1.5">
                @if ($eglise->actif)
                <span class="inline-flex rounded-full bg-emerald-100/90 dark:bg-emerald-900/40 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:text-emerald-200">Active</span>
                @else
                <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-xs font-medium text-slate-500">Inactive</span>
                @endif
            </dd>
        </div>
    </dl>

    @if ($eglise->indicateurs_financiers)
    <div class="adventiste-card-pro-static p-6 sm:p-7">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Indicateurs financiers</h2>
        <p class="text-xs text-slate-500 dark:text-slate-500 mb-4">Données de référence (saisie JSON)</p>
        <pre class="text-xs font-mono overflow-x-auto text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-950/60 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/80">{{ json_encode($eglise->indicateurs_financiers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif
</div>
@endsection
