@extends('layouts.app')

@section('page-title', 'Éditer — ' . $departement->nom)

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ $eglise->nom }}</span>
@endsection

@section('content')
@include('parametres._nav')

<div class="adventiste-card-pro-static max-w-3xl p-6 sm:p-8">
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
        Modifiez les détails du ministère ou département.
    </p>
    <form method="post" action="{{ route('parametres.eglises.departements.update', [$eglise, $departement]) }}" class="space-y-8">
        @csrf
        @method('PUT')
        @include('parametres.eglises.departements._form')
        <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
            <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
            <a href="{{ route('parametres.eglises.departements.index', $eglise) }}" class="adventiste-btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
