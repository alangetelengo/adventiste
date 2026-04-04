@extends('layouts.app')

@section('page-title', 'Nouveau rapport membres')

@section('page-title-info')
    {{ auth()->user()->egliseLocale?->nom }}
@endsection

@section('content')
    <div class="max-w-2xl adventiste-card-pro-static p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
            Le rapport synthétise les mouvements des membres (baptêmes, transferts, statuts) pour la période choisie, puis peut être soumis à la mission.
        </p>
        <form method="post" action="{{ route('secretariat.rapports-membres.store') }}" class="space-y-5" data-offline-queue>
            @csrf
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="type_periode" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Type</label>
                    <select name="type_periode" id="type_periode" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        <option value="mensuel" @selected(old('type_periode', 'mensuel') === 'mensuel')>Mensuel</option>
                        <option value="annuel" @selected(old('type_periode') === 'annuel')>Annuel</option>
                    </select>
                </div>
                <div id="bloc_mois">
                    <label for="mois" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mois</label>
                    <select name="mois" id="mois" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
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

            <div>
                <label for="notes_locales" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes du secrétaire (optionnel)</label>
                <textarea name="notes_locales" id="notes_locales" rows="4" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">{{ old('notes_locales') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
                    Générer le rapport
                </button>
                <a href="{{ route('secretariat.rapports-membres.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                    Annuler
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        (function () {
            const type = document.getElementById('type_periode');
            const blocMois = document.getElementById('bloc_mois');
            if (!type || !blocMois) return;
            const refresh = () => {
                blocMois.style.display = type.value === 'annuel' ? 'none' : '';
            };
            type.addEventListener('change', refresh);
            refresh();
        })();
    </script>
    @endpush
@endsection
