@extends('layouts.app')

@section('page-title', "Enregistrement d'une recette de l'église")

@section('page-title-info')
    Contributeur: {{ $membre->nom }} {{ $membre->prenom }} — {{ $membre->egliseLocale?->nom }}
@endsection

@section('content')
    <div class="max-w-lg adventiste-card-pro-static p-6 sm:p-8">
        @if ($typesRecette->isEmpty())
            <p class="text-sm text-amber-800 dark:text-amber-200">Aucun type de recette actif. Configurez-les dans les paramètres mission.</p>
        @else
            <form method="post" action="{{ route('finances.recaps.contribution-membre.store', $membre) }}" class="space-y-5" data-offline-queue>
                @csrf
                <div>
                    <label for="date_sabbat" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Date du sabbat</label>
                    <input type="date" name="date_sabbat" id="date_sabbat" value="{{ old('date_sabbat') }}" required
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    @error('date_sabbat')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="type_recette_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Type de recette</label>
                    <select name="type_recette_id" id="type_recette_id" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        <option value="">—</option>
                        @foreach ($typesRecette as $t)
                            <option value="{{ $t->id }}" data-categorie="{{ $t->categorie }}" @selected((string) old('type_recette_id') === (string) $t->id)>{{ $t->libelle }}</option>
                        @endforeach
                    </select>
                    @error('type_recette_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="montant" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Montant (FCFA)</label>
                    @php
                        $montantOld = old('montant');
                        $montantAffiche = $montantOld;
                        if ($montantOld !== null && $montantOld !== '') {
                            $montantNet = str_replace([' ', ','], ['', '.'], (string) $montantOld);
                            if (is_numeric($montantNet)) {
                                $montantAffiche = number_format((float) $montantNet, 2, '.', ' ');
                            }
                        }
                    @endphp
                    <input type="text" name="montant" id="montant" value="{{ $montantAffiche }}" required inputmode="decimal" placeholder="10 000"
                        class="js-montant-fcfa w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Format attendu : 10 000.00</p>
                    @error('montant')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div id="bloc-don-only">
                    <label for="designation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Motif / désignation (optionnel)</label>
                    <input type="text" name="designation" id="designation" value="{{ old('designation') }}"
                        class="js-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                        placeholder="Ex: Don construction, offrande spéciale...">
                </div>
                <div id="bloc-don-only-grid-1" class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="mode_don" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mode du don</label>
                        <select name="mode_don" id="mode_don" class="js-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                            <option value="argent" @selected(old('mode_don', 'argent') === 'argent')>Argent</option>
                            <option value="nature" @selected(old('mode_don') === 'nature')>Nature</option>
                        </select>
                    </div>
                    <div>
                        <label for="destination_don" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Destination du don</label>
                        <select name="destination_don" id="destination_don" class="js-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                            <option value="locale" @selected(old('destination_don', 'locale') === 'locale')>Reste local</option>
                            <option value="mission" @selected(old('destination_don') === 'mission')>Mission sans partage</option>
                        </select>
                    </div>
                </div>
                <div id="bloc-don-only-grid-2" class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="quantite_nature" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Quantité nature (optionnel)</label>
                        <input type="number" step="0.01" min="0" name="quantite_nature" id="quantite_nature" value="{{ old('quantite_nature') }}"
                            class="js-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="unite_nature" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Unité (optionnel)</label>
                        <input type="text" name="unite_nature" id="unite_nature" value="{{ old('unite_nature') }}"
                            class="js-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                            placeholder="Sac, kg, carton...">
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">Enregistrer</button>
                    <a href="{{ route('membres.show', $membre) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline inline-flex items-center">Annuler</a>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    (function() {
        function toggleDonFields() {
            var select = document.getElementById('type_recette_id');
            if (!select) return;
            var option = select.options[select.selectedIndex];
            var cat = option ? option.getAttribute('data-categorie') : '';
            var isDon = String(cat || '') === 'don';
            ['bloc-don-only', 'bloc-don-only-grid-1', 'bloc-don-only-grid-2'].forEach(function(id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.style.display = isDon ? '' : 'none';
            });
            document.querySelectorAll('.js-don-input').forEach(function(input) {
                input.disabled = !isDon;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('type_recette_id');
            if (select) {
                select.addEventListener('change', toggleDonFields);
            }
            toggleDonFields();
        });
    })();
</script>
@endpush
