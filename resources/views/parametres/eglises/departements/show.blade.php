@extends('layouts.app')

@section('page-title', $departement->nom)

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ $eglise->nom }}</span>
@endsection

@section('content')
@include('parametres._nav')

<div class="max-w-3xl space-y-6">
    <div class="flex flex-wrap gap-3">
        @can('update', $departement)
        <a href="{{ route('parametres.eglises.departements.edit', [$eglise, $departement]) }}" class="adventiste-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            Modifier
        </a>
        @endcan
        <a href="{{ route('parametres.eglises.departements.index', $eglise) }}" class="adventiste-btn-secondary">← Liste des ministères</a>
    </div>

    <dl class="adventiste-card-pro-static p-6 sm:p-7 grid gap-5 sm:grid-cols-2 text-sm">
        <div class="sm:col-span-2 pb-4 border-b border-slate-200/80 dark:border-slate-600/50">
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ministère/Département</dt>
            <dd class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">{{ $departement->nom }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Code unique</dt>
            <dd class="mt-1.5 font-mono text-xs text-slate-800 dark:text-slate-100">{{ $departement->code_unique }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Statut</dt>
            <dd class="mt-1.5">
                @if ($departement->actif)
                <span class="inline-flex rounded-full bg-emerald-100/90 dark:bg-emerald-900/40 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:text-emerald-200">Actif</span>
                @else
                <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-xs font-medium text-slate-500">Inactif</span>
                @endif
            </dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Église</dt>
            <dd class="mt-1.5 font-medium text-slate-800 dark:text-slate-100">{{ $eglise->nom }}</dd>
        </div>
    </dl>
</div>
@endsection
