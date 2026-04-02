@extends('layouts.app')

@section('title', 'Tableau de bord — ' . config('app.name'))

@section('page-title', 'Tableau de bord')
@section('page-title-info')
    <p class="text-slate-500 dark:text-slate-400">Vue d’ensemble — contenu statique en attendant les modules.</p>
@endsection

@section('content')
<div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 p-6 shadow-sm">
    <p class="text-slate-700 dark:text-slate-300 leading-relaxed">
        Bienvenue dans l’interface de gestion de l’église. La structure (barre latérale, en-tête, pied de page) reprend le gabarit du projet E-Ged ; les entrées de menu sont pour l’instant statiques.
    </p>
</div>
@endsection
