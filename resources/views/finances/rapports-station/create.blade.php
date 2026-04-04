@extends('layouts.app')

@section('page-title', 'Nouveau rapport de station')

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom }}</span>
@endsection

@section('content')
<div class="adventiste-card-pro-static max-w-3xl p-6 sm:p-8">
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
        Créer un rapport de station pour la période spécifiée. Les lignes de ventilation seront initialisées selon les règles de votre mission.
    </p>
    <form method="post" action="{{ route('finances.rapports-station.store') }}" class="space-y-8" data-offline-queue>
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="annee" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Année</label>
                <input type="number" name="annee" id="annee" min="2000" max="2100" value="{{ old('annee', now()->year) }}" required class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
                @error('annee')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="mois" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Mois</label>
                <select name="mois" id="mois" required class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    @php
                    $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                    @endphp
                    @for ($m = 1; $m <= 12; $m++) <option value="{{ $m }}" @selected(old('mois', now()->month) == $m)>
                        {{ $months[$m] ?? "Mois $m" }}
                        </option>
                        @endfor
                </select>
                @error('mois')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
            <button type="submit" class="adventiste-btn-primary">Créer le rapport</button>
            <a href="{{ route('finances.rapports-station.index') }}" class="adventiste-btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
