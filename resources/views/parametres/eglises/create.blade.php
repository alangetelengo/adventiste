@extends('layouts.app')

@section('page-title', 'Nouvelle église locale')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom }}</span>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static max-w-3xl p-6 sm:p-8">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
            Renseignez l’identité de la paroisse. Le <strong class="text-slate-800 dark:text-slate-200">code unique</strong> sert aux exports et aux liaisons techniques ; il doit rester stable.
        </p>
        <form method="post" action="{{ route('parametres.eglises.store') }}" class="space-y-8">
            @csrf
            @include('parametres.eglises._form', ['eglise' => null])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Créer l’église</button>
                <a href="{{ route('parametres.eglises.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
