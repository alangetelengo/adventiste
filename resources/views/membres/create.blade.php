@extends('layouts.app')

@section('page-title', 'Nouveau membre')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom ?? auth()->user()->egliseLocale?->nom }}</span>
@endsection

@section('content')
    <div class="adventiste-card-pro-static w-full max-w-6xl p-6 sm:p-8">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
            Renseignez les informations disponibles. Les champs vides pourront être complétés plus tard.
        </p>
        <form method="post" action="{{ route('membres.store') }}" class="space-y-8">
            @csrf
            @include('membres._form', ['membre' => null])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('membres.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
