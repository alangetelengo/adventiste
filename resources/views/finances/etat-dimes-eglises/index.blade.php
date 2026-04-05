@extends('layouts.app')

@section('page-title', __('finances.etat_dimes.title'))

@section('page-title-info')
{{ __('finances.etat_dimes.subtitle') }}
@endsection

@section('btn-create')
<a href="{{ route('finances.etat-dimes-eglises.export-pdf', ['annee' => $annee]) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline inline-flex items-center gap-2">
    {{ __('finances.common.export_pdf') }}
</a>
<a href="{{ route('finances.etat-dimes-eglises.export-excel', ['annee' => $annee]) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline inline-flex items-center gap-2">
    {{ __('finances.common.export_excel') }}
</a>
<a href="{{ route('finances.etat-dimes-eglises.impression', ['annee' => $annee]) }}" target="_blank" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline inline-flex items-center gap-2">
    {{ __('finances.common.preview_print') }}
</a>
@endsection

@section('content')
<div class="adventiste-card-pro-static mb-6 p-5 sm:p-6">
    <form method="get" action="{{ route('finances.etat-dimes-eglises.index') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="annee" class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('finances.common.year') }}</label>
            <input type="number" id="annee" name="annee" value="{{ $annee }}" min="2000" max="2100" class="w-32 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
        </div>
        <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
            {{ __('finances.common.show') }}
        </button>
    </form>
</div>

<div class="adventiste-card-pro-static overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[1200px]">
            <thead>
                <tr class="border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="bg-emerald-100/80 dark:bg-emerald-900/40 px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_num') }}</th>
                    <th class="bg-emerald-100/80 dark:bg-emerald-900/40 px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_church') }}</th>
                    <th class="bg-emerald-100/80 dark:bg-emerald-900/40 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_objectif', ['year' => $annee]) }}</th>
                    <th class="bg-sky-100/80 dark:bg-sky-900/40 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_dimes_collected', ['year' => $annee]) }}</th>
                    <th class="bg-sky-100/80 dark:bg-sky-900/40 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_offrandes_collected', ['year' => $annee]) }}</th>
                    <th class="bg-orange-100/80 dark:bg-orange-900/40 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_dimes_prev_year', ['year' => $annee - 1]) }}</th>
                    <th class="bg-orange-100/80 dark:bg-orange-900/40 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_ecart', ['y1' => $annee, 'y2' => $annee - 1]) }}</th>
                    <th class="bg-orange-100/80 dark:bg-orange-900/40 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_avg_monthly') }}</th>
                    <th class="bg-yellow-100/90 dark:bg-yellow-900/40 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_member_count') }}</th>
                    <th class="bg-yellow-100/90 dark:bg-yellow-900/40 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ __('finances.etat_dimes.col_pct_apport') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                @forelse ($lignes as $ligne)
                <tr class="hover:bg-emerald-50/40 dark:hover:bg-slate-700/40">
                    <td class="px-4 py-2.5">{{ $ligne['rang'] }}</td>
                    <td class="px-4 py-2.5 font-semibold">{{ $ligne['eglise_nom'] }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($ligne['objectif_dimes'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($ligne['dimes_collectees'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($ligne['offrandes_collectees'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($ligne['dimes_annee_precedente'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums {{ $ligne['ecart_dimes'] < 0 ? 'text-red-700 dark:text-red-300 font-semibold' : 'text-emerald-700 dark:text-emerald-300 font-semibold' }}">
                        {{ number_format($ligne['ecart_dimes'], 2, ',', ' ') }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($ligne['dimes_moyenne_mensuelle'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($ligne['nombre_membres'], 0, ',', ' ') }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums font-semibold">{{ number_format($ligne['pourcentage_apport'], 2, ',', ' ') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                        {{ __('finances.common.no_churches_mission') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="border-t-2 border-slate-300 dark:border-slate-500">
                <tr class="bg-slate-100/90 dark:bg-slate-700/60 font-semibold">
                    <td colspan="2" class="px-4 py-3 text-left">{{ __('finances.common.total_footer') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totaux['objectif_dimes'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totaux['dimes_collectees'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totaux['offrandes_collectees'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totaux['dimes_annee_precedente'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums {{ $totaux['ecart_dimes'] < 0 ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ number_format($totaux['ecart_dimes'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totaux['dimes_moyenne_mensuelle'], 2, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totaux['nombre_membres'], 0, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totaux['pourcentage_apport'], 2, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
