@extends('layouts.app')

@section('title', $pageTitle . ' — ' . config('app.name'))

@section('page-title', $pageTitle)

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ $pageDescription }}</span>
@endsection

@section('content')
    <div class="adventiste-card-pro-static max-w-2xl p-8 sm:p-10 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-linear-to-br from-emerald-500/15 to-violet-500/15 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 mb-6">
            <svg class="w-8 h-8 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <p class="text-slate-700 dark:text-slate-300 leading-relaxed">
            Cette section est en cours de conception. Les fonctionnalités décrites ci-dessus seront disponibles dans une prochaine version.
        </p>
        <a href="{{ route('tableau-de-bord') }}" class="adventiste-btn-secondary mt-8 inline-flex">Retour au tableau de bord</a>
    </div>
@endsection
