@extends('layouts.app')

@php
$semainesSabbat = \App\Models\RecapSabbatEglise::libellesSemainesSabbat();
@endphp

@section('content-container-class', 'w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10')

@section('page-title', 'Saisie simplifiée — sabbat')

@section('page-title-info')
Saisie rapide des recettes du sabbat — {{ auth()->user()->egliseLocale?->nom }}
@endsection

@section('content')
<div class="grid w-full min-h-[calc(100dvh-9.5rem)] grid-cols-1 gap-6 md:grid-cols-2 md:gap-8 md:items-stretch">
    <div class="flex h-full min-h-[calc(100dvh-9.5rem)] flex-col md:min-h-0">
        <div class="flex h-full min-h-0 flex-1 flex-col adventiste-card-pro-static p-6 sm:p-8">
            @if ($typesRecette->isEmpty())
            <p class="text-sm text-amber-800 dark:text-amber-200 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50/80 dark:bg-amber-950/30 p-4">
                Aucun type de recette actif pour votre mission. Un administrateur ou le trésorier de mission doit en créer dans
                <a href="{{ route('parametres.types-recette.index') }}" class="font-semibold underline">Paramètres → Types de recette</a>.
            </p>
            @else
            <form method="post" action="{{ route('finances.recaps.store') }}" class="flex min-h-0 flex-1 flex-col space-y-5" data-offline-queue>
                @csrf
                <div>
                    <label for="date_sabbat" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Date du sabbat</label>
                    <input type="date" name="date_sabbat" id="date_sabbat" value="{{ old('date_sabbat') }}" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-slate-900 dark:text-slate-100">
                    @error('date_sabbat')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="rounded-lg border border-emerald-200/80 dark:border-emerald-800/40 bg-emerald-50/50 dark:bg-emerald-950/20 p-4 space-y-3">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Recettes du culte (totaux assemblée)</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Une ligne = un type + un montant. Si un sabbat existe déjà à cette date, les lignes assemblée brouillon seront remplacées automatiquement.</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-600">
                                    <th class="py-2 pr-3">Type de recette</th>
                                    <th class="py-2 pr-3">Montant (FCFA)</th>
                                    <th class="py-2 pr-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="lignes-assemblee-body">
                                @php $rowsA = old('lignes_assemblee', [['type_recette_id' => '', 'montant' => '']]); @endphp
                                @foreach ($rowsA as $idx => $row)
                                <tr class="border-b border-slate-100 dark:border-slate-700/80">
                                    <td class="py-2 pr-2 align-top">
                                        <select name="lignes_assemblee[{{ $idx }}][type_recette_id]" class="w-full rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-sm">
                                            <option value="">—</option>
                                            @foreach ($typesRecette as $t)
                                            <option value="{{ $t->id }}" @selected((string)($row['type_recette_id'] ?? '' )===(string) $t->id)>{{ $t->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <input type="hidden" name="lignes_assemblee[{{ $idx }}][departement_ministere_id]" value="">
                                    <td class="py-2 pr-2 align-top">
                                        <input type="text" name="lignes_assemblee[{{ $idx }}][montant]" value="{{ $row['montant'] ?? '' }}" inputmode="decimal" autocomplete="off" placeholder="0" class="js-montant-fcfa w-full min-w-36 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5">
                                    </td>
                                    <td class="py-2 pr-2 align-top text-right">
                                        <button type="button" class="js-remove-assemblee-line rounded border border-red-300 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/30">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="btn-add-assemblee" class="text-sm font-semibold text-[#00b464] dark:text-emerald-400 hover:underline">+ Ajouter une ligne</button>
                    @error('lignes_assemblee')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-auto flex flex-wrap gap-3 border-t border-slate-200/80 pt-6 dark:border-slate-600/60">
                    <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
                        Enregistrer le sabbat
                    </button>
                    <a href="{{ route('finances.recaps.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                        Annuler
                    </a>
                </div>
            </form>
            @endif
        </div>
    </div>
    <div class="flex h-full min-h-[calc(100dvh-9.5rem)] flex-col md:min-h-0">
        @include('finances.recaps._aide-nouveau-recap')
    </div>
</div>

<template id="tpl-ligne-assemblee">
    <tr class="border-b border-slate-100 dark:border-slate-700/80">
        <td class="py-2 pr-2 align-top">
            <select name="lignes_assemblee[__I__][type_recette_id]" class="w-full rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-sm">
                <option value="">—</option>
                @foreach ($typesRecette as $t)
                <option value="{{ $t->id }}">{{ $t->libelle }}</option>
                @endforeach
            </select>
        </td>
        <input type="hidden" name="lignes_assemblee[__I__][departement_ministere_id]" value="">
        <td class="py-2 pr-2 align-top">
            <input type="text" name="lignes_assemblee[__I__][montant]" value="" inputmode="decimal" autocomplete="off" placeholder="0" class="js-montant-fcfa w-full min-w-36 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5">
        </td>
        <td class="py-2 pr-2 align-top text-right">
            <button type="button" class="js-remove-assemblee-line rounded border border-red-300 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/30">
                Supprimer
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
    (function() {
        var init = function() {
            var button = document.getElementById('btn-add-assemblee');
            var body = document.getElementById('lignes-assemblee-body');
            var tpl = document.getElementById('tpl-ligne-assemblee');
            if (!button || !body || !tpl || button.dataset.boundAddLine === '1') {
                return;
            }

            button.dataset.boundAddLine = '1';
            var nextIdx = @json(count(old('lignes_assemblee', [['type_recette_id' => '', 'montant' => '']])));

            button.addEventListener('click', function() {
                var html = tpl.innerHTML.replace(/__I__/g, String(nextIdx));
                body.insertAdjacentHTML('beforeend', html);
                nextIdx += 1;
            });

            body.addEventListener('click', function(event) {
                var target = event.target;
                if (!(target instanceof HTMLElement)) {
                    return;
                }
                var removeBtn = target.closest('.js-remove-assemblee-line');
                if (!removeBtn) {
                    return;
                }
                var row = removeBtn.closest('tr');
                if (!row) {
                    return;
                }
                var rows = body.querySelectorAll('tr');
                if (rows.length <= 1) {
                    row.querySelectorAll('input').forEach(function(input) {
                        if (input instanceof HTMLInputElement) {
                            input.value = '';
                        }
                    });
                    row.querySelectorAll('select').forEach(function(select) {
                        if (select instanceof HTMLSelectElement) {
                            select.value = '';
                        }
                    });
                    return;
                }
                row.remove();
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, {
                once: true
            });
        } else {
            init();
        }
    })();
</script>
@endpush
