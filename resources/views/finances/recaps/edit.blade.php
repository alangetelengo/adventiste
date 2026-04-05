@extends('layouts.app')

@section('content-container-class', 'w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10')

@section('page-title', __('finances.recaps.page_edit_title'))

@section('page-title-info')
{{ $recap->date_sabbat->translatedFormat('d M Y') }} — {{ $recap->egliseLocale->nom }}
@endsection

@php
$semainesSabbat = \App\Models\RecapSabbatEglise::libellesSemainesSabbat();
$semaineForm = old('semaine_sabbat', $recap->semaine_sabbat);
$calcRecap = \App\Services\Finances\CalculateurMontantsRecapSabbat::pour($recap);
$labelsStatutLigne = \App\Models\LigneDimeOffrandeRecap::labelsStatutLigne();
$labelsOrigine = \App\Models\LigneDimeOffrandeRecap::labelsOrigine();
@endphp

@section('content')
<div class="grid w-full gap-6 md:grid-cols-2 md:gap-8 md:items-start">
    <div class="space-y-6 md:col-span-1">
        @if (! empty($lignesFigees))
        <div class="adventiste-card-pro-static p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-3">{{ __('finances.recaps.frozen_lines_title') }}</h2>
            <div class="overflow-x-auto text-sm">
                <table class="min-w-full">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-600">
                            <th class="py-2 pr-2">{{ __('finances.recaps.col_type') }}</th>
                            <th class="py-2 pr-2">{{ __('finances.recaps.col_origin') }}</th>
                            <th class="py-2 pr-2">{{ __('finances.recaps.col_amount') }}</th>
                            <th class="py-2">{{ __('finances.recaps.col_status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($lignesFigees as $lf)
                        <tr>
                            <td class="py-2 pr-2">{{ $lf->typeRecette?->libelle ?? '—' }}</td>
                            <td class="py-2 pr-2">{{ $labelsOrigine[$lf->origine] ?? $lf->origine }}</td>
                            <td class="py-2 pr-2 tabular-nums">{{ \App\Support\MontantFcfa::formatDisplay((float) $lf->dimes + (float) $lf->offrandes) }}</td>
                            <td class="py-2">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">{{ $labelsStatutLigne[$lf->statut_ligne] ?? $lf->statut_ligne }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @can('update', $recap)
        <form method="post" action="{{ route('finances.recaps.update', $recap) }}" class="space-y-6" data-offline-queue>
            @csrf
            @method('PUT')

            <div class="adventiste-card-pro-static p-6 space-y-4">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ __('finances.recaps.header_block') }}</h2>
                <p class="text-sm text-slate-500">{!! __('finances.recaps.date_sabbat_locked', ['date' => $recap->date_sabbat->format('d/m/Y')]) !!}</p>
                <div>
                    <label for="semaine_sabbat" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('finances.recaps.week_sabbat_label') }}</label>
                    <select name="semaine_sabbat" id="semaine_sabbat" class="w-full max-w-md rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        <option value="" @selected($semaineForm===null || $semaineForm==='' )>—</option>
                        @foreach ($semainesSabbat as $num => $libelle)
                        <option value="{{ $num }}" @selected((string) $semaineForm===(string) $num)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                    @error('semaine_sabbat')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="adventiste-card-pro-static p-6">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">{{ __('finances.recaps.assemblee_editable') }}</h2>
                <p class="text-sm text-slate-500 mb-4">{{ __('finances.recaps.assemblee_hint') }}</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-600">
                                <th class="py-2 pr-2">{{ __('finances.recaps.col_type') }}</th>
                                <th class="py-2 pr-2">{{ __('finances.recaps.amount_fcfa') }}</th>
                                <th class="py-2 pr-2 text-right">{{ __('finances.common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="body-assemblee">
                            @foreach ($lignesAssembleeForm as $idx => $ligne)
                            <tr class="border-b border-slate-100 dark:border-slate-700/80">
                                <td class="py-2 pr-2 align-top">
                                    <select name="lignes_assemblee[{{ $idx }}][type_recette_id]" class="w-full rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-sm">
                                        <option value="">—</option>
                                        @foreach ($typesRecette as $t)
                                        <option value="{{ $t->id }}" @selected((string)($ligne['type_recette_id'] ?? '' )===(string) $t->id)>{{ $t->libelle }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <input type="hidden" name="lignes_assemblee[{{ $idx }}][departement_ministere_id]" value="">
                                <td class="py-2 pr-2 align-top">
                                    @php
                                    $mo = old('lignes_assemblee.'.$idx.'.montant');
                                    $mv = $mo !== null ? $mo : ($ligne['montant'] ?? '');
                                    $mAff = ($mv !== '' && $mv !== null && !is_numeric($mo)) ? $mv : (($mv !== '' && $mv !== null) ? \App\Support\MontantFcfa::formatDisplay($mv) : '');
                                    @endphp
                                    <input type="text" name="lignes_assemblee[{{ $idx }}][montant]" value="{{ $mAff }}" inputmode="decimal" class="js-montant-fcfa w-full min-w-36 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5">
                                </td>
                                <td class="py-2 pr-2 align-top text-right">
                                    <button type="button" class="js-remove-assemblee-line rounded border border-red-300 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/30">
                                        {{ __('finances.common.delete_line') }}
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" id="btn-add-assemblee" class="mt-2 text-sm font-semibold text-[#00b464] dark:text-emerald-400 hover:underline">{{ __('finances.recaps.add_line_assemblee') }}</button>
            </div>

            <div class="adventiste-card-pro-static p-4">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    {!! __('finances.recaps.individual_only_members_html') !!}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#00b464] text-white font-semibold hover:bg-[#00a055] text-sm">{{ __('ui.save') }}</button>
                <a href="{{ route('finances.recaps.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border-2 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-sm no-underline">{{ __('finances.common.back') }}</a>
            </div>
        </form>
        @else
        <div class="adventiste-card-pro-static p-6 text-sm text-slate-600 dark:text-slate-400">
            {!! __('finances.recaps.read_only_treasurer') !!}
        </div>
        @endcan

        <div class="flex flex-wrap gap-3"></div>
    </div>

    <div class="md:col-span-1 space-y-6">
        <div class="rounded-xl border border-emerald-200/80 dark:border-emerald-800/40 bg-emerald-50/40 dark:bg-emerald-950/20 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">{{ __('finances.recaps.totals_preview_title') }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{!! __('finances.recaps.totals_preview_hint') !!}</p>
            <dl class="grid gap-3 text-sm sm:grid-cols-1">
                <div class="rounded-lg border border-slate-200/80 dark:border-slate-600/60 bg-white/80 dark:bg-slate-800/60 px-3 py-2">
                    <dt class="text-xs font-medium text-slate-500">{{ __('finances.recaps.total_dimes') }}</dt>
                    <dd class="mt-0.5 font-semibold tabular-nums">{{ \App\Support\MontantFcfa::formatDisplay($calcRecap->totalDimes()) }}</dd>
                </div>
                <div class="rounded-lg border border-slate-200/80 dark:border-slate-600/60 bg-white/80 dark:bg-slate-800/60 px-3 py-2">
                    <dt class="text-xs font-medium text-slate-500">{{ __('finances.recaps.cultual_offrandes') }}</dt>
                    <dd class="mt-0.5 font-semibold tabular-nums">{{ \App\Support\MontantFcfa::formatDisplay($calcRecap->totalOffrandesCultuelles()) }}</dd>
                </div>
                <div class="rounded-lg border border-slate-200/80 dark:border-slate-600/60 bg-white/80 dark:bg-slate-800/60 px-3 py-2">
                    <dt class="text-xs font-medium text-slate-500">{{ __('finances.recaps.dons') }}</dt>
                    <dd class="mt-0.5 font-semibold tabular-nums">{{ \App\Support\MontantFcfa::formatDisplay($calcRecap->totalDons()) }}</dd>
                </div>
                <div class="rounded-lg border border-slate-200/80 dark:border-slate-600/60 bg-white/80 dark:bg-slate-800/60 px-3 py-2">
                    <dt class="text-xs font-medium text-slate-500">{{ __('finances.recaps.transfer_to_mission') }}</dt>
                    <dd class="mt-0.5 font-semibold text-emerald-800 dark:text-emerald-300 tabular-nums">{{ \App\Support\MontantFcfa::formatDisplay($calcRecap->totalATransfererMission()) }}</dd>
                </div>
            </dl>
        </div>
        @include('finances.recaps._aide-edition-recap')
    </div>
</div>

<template id="tpl-assemblee">
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
            <input type="text" name="lignes_assemblee[__I__][montant]" value="" inputmode="decimal" class="js-montant-fcfa w-full min-w-36 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5">
        </td>
        <td class="py-2 pr-2 align-top text-right">
            <button type="button" class="js-remove-assemblee-line rounded border border-red-300 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/30">
                {{ __('finances.common.delete_line') }}
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
            var body = document.getElementById('body-assemblee');
            var tpl = document.getElementById('tpl-assemblee');
            if (!button || !body || !tpl || button.dataset.boundAddLine === '1') {
                return;
            }

            button.dataset.boundAddLine = '1';
            var ia = @json(count($lignesAssembleeForm));

            button.addEventListener('click', function() {
                body.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__I__/g, String(ia)));
                ia += 1;
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
