@extends('layouts.app')

@section('page-title', 'Modifier un baptême')

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ $bapteme->nom }} {{ $bapteme->prenom }}</span>
@endsection

@section('content')
<div class="adventiste-card-pro-static w-full max-w-5xl p-6 sm:p-8">
    <form method="post" action="{{ route('baptemes.update', $bapteme) }}" class="space-y-8">
        @csrf
        @method('PUT')
        @include('baptemes._form', ['bapteme' => $bapteme])
        <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
            <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
            <a href="{{ route('baptemes.show', $bapteme) }}" class="adventiste-btn-secondary">Fiche</a>
            <a href="{{ route('baptemes.index') }}" class="adventiste-btn-secondary">Liste</a>
        </div>
    </form>
</div>
@endsection

