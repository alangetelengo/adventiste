@extends('layouts.app')

@section('page-title', $groupe->nom)

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">Groupe mission — {{ auth()->user()->mission?->nom }}</span>
@endsection

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        @can('update', $groupe)
            <a href="{{ route('parametres.groupes-mission.edit', $groupe) }}" class="adventiste-btn-primary">Modifier</a>
        @endcan
        <a href="{{ route('parametres.groupes-mission.index') }}" class="adventiste-btn-secondary">Liste des groupes</a>
    </div>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static p-6 sm:p-8 max-w-2xl">
        <dl class="space-y-4 text-sm">
            <div class="flex flex-wrap justify-between gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <dt class="text-slate-500 dark:text-slate-400">Code unique</dt>
                <dd class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $groupe->code_unique }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <dt class="text-slate-500 dark:text-slate-400">Statut</dt>
                <dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $groupe->actif ? 'Actif' : 'Inactif' }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <dt class="text-slate-500 dark:text-slate-400">Membres rattachés</dt>
                <dd class="font-semibold tabular-nums text-slate-900 dark:text-slate-100">{{ $groupe->membres_count }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <dt class="text-slate-500 dark:text-slate-400">Lignes finances mission</dt>
                <dd class="font-semibold tabular-nums text-slate-900 dark:text-slate-100">{{ $groupe->entrees_financieres_count }}</dd>
            </div>
        </dl>
        <p class="mt-6 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            Pour rattacher des membres, utilisez le champ « Groupe mission » sur les <a href="{{ route('membres.index') }}" class="font-semibold text-[#00b464] hover:underline">fiches membres</a>.
        </p>
    </div>
@endsection
