@extends('layouts.app')

@section('page-title', 'Modifier l’église')

@section('page-title-info')
    <span class="font-mono text-sm text-slate-500 dark:text-slate-400">{{ $eglise->code_unique }}</span>
    <span class="mx-2 text-slate-300 dark:text-slate-600">·</span>
    <span class="text-slate-600 dark:text-slate-400">{{ $eglise->nom }}</span>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static max-w-3xl p-6 sm:p-8">
        <form method="post" action="{{ route('parametres.eglises.update', $eglise) }}" class="space-y-8">
            @csrf
            @method('PUT')
            @include('parametres.eglises._form', ['eglise' => $eglise])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('parametres.eglises.show', $eglise) }}" class="adventiste-btn-secondary">Fiche</a>
                <a href="{{ route('parametres.eglises.index') }}" class="adventiste-btn-secondary">Liste</a>
            </div>
        </form>
    </div>
@endsection
