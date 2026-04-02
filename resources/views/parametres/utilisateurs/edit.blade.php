@extends('layouts.app')

@section('page-title', 'Modifier l’utilisateur')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ $utilisateur->email }}</span>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static max-w-3xl p-6 sm:p-8">
        <form method="post" action="{{ route('parametres.utilisateurs.update', $utilisateur) }}" class="space-y-8">
            @csrf
            @method('PUT')
            @include('parametres.utilisateurs._form', ['utilisateur' => $utilisateur])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('parametres.utilisateurs.index') }}" class="adventiste-btn-secondary">Liste</a>
            </div>
        </form>
    </div>
@endsection
