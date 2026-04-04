@extends('layouts.app')

@section('page-title', $district->nom)

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">District — {{ auth()->user()->mission?->nom }}</span>
@endsection

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        @can('update', $district)
            <a href="{{ route('parametres.districts.edit', $district) }}" class="adventiste-btn-primary">Modifier</a>
        @endcan
        <a href="{{ route('parametres.districts.index') }}" class="adventiste-btn-secondary">Liste des districts</a>
    </div>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static p-6 sm:p-8 max-w-2xl">
        <dl class="space-y-4 text-sm">
            <div class="flex flex-wrap justify-between gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <dt class="text-slate-500 dark:text-slate-400">Églises rattachées</dt>
                <dd class="font-semibold tabular-nums text-slate-900 dark:text-slate-100">{{ $district->eglises_locales_count }}</dd>
            </div>
        </dl>
        <p class="mt-6 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            Pour rattacher ou modifier les paroisses, ouvrez <a href="{{ route('parametres.eglises.index') }}" class="font-semibold text-[#00b464] hover:underline">Églises locales</a> et choisissez ce district sur chaque fiche.
        </p>
    </div>
@endsection
