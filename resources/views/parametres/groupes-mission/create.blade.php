@extends('layouts.app')

@section('page-title', 'Nouveau groupe mission')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom }}</span>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static max-w-3xl p-6 sm:p-8">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
            Les groupes servent à classer les membres et à ventiler certaines entrées au niveau mission. Le code unique est partagé avec le module finances.
        </p>
        <form method="post" action="{{ route('parametres.groupes-mission.store') }}" class="space-y-8">
            @csrf
            @include('parametres.groupes-mission._form', ['groupe' => null])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Créer le groupe</button>
                <a href="{{ route('parametres.groupes-mission.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
