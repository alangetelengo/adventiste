@extends('layouts.app')

@section('page-title', 'Nouveau rapport mensuel')

@section('page-title-info')
    {{ auth()->user()->egliseLocale?->nom }}
@endsection

@section('content')
    <div class="max-w-xl adventiste-card-pro-static p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
            Le rapport est calculé automatiquement à partir des récaps du sabbat du mois choisi (dîmes, parts mission, synthèse).
        </p>
        <form method="post" action="{{ route('finances.rapports-mensuels.store') }}" class="space-y-5" data-offline-queue>
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="mois" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mois</label>
                    <select name="mois" id="mois" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        @foreach ([1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'] as $num => $label)
                            <option value="{{ $num }}" @selected((int) old('mois', now()->month) === $num)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('mois')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="annee" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Année</label>
                    <input type="number" name="annee" id="annee" min="2000" max="2100" required value="{{ old('annee', now()->year) }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    @error('annee')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
                    Générer le rapport
                </button>
                <a href="{{ route('finances.rapports-mensuels.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
