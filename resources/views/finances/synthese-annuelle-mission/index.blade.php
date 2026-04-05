@extends('layouts.app')

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('page-title', __('finances.synthese_annuelle.title'))

@section('page-title-info')
{{ __('finances.synthese_annuelle.subtitle', ['year' => $annee]) }}
@endsection

@section('btn-create')
<a href="{{ route('finances.synthese-annuelle-mission.export-pdf', ['annee' => $annee]) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline inline-flex items-center gap-2">
    {{ __('finances.common.export_pdf') }}
</a>
<a href="{{ route('finances.synthese-annuelle-mission.export-excel', ['annee' => $annee]) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline inline-flex items-center gap-2">
    {{ __('finances.common.export_excel') }}
</a>
<a href="{{ route('finances.synthese-annuelle-mission.impression', ['annee' => $annee]) }}" target="_blank" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline inline-flex items-center gap-2">
    {{ __('finances.common.preview_print') }}
</a>
@endsection

@php
    $keysMois = array_keys($nomsMois);
    $sum = fn (array $row) => array_sum($row);
    $fmt = fn (float $v) => number_format($v, 2, ',', ' ');
    $fmtPct = fn (float $v) => number_format($v, 2, ',', ' ').'%';
@endphp

@section('content')
<div class="adventiste-card-pro-static mb-5 p-5 sm:p-6">
    <form method="get" action="{{ route('finances.synthese-annuelle-mission.index') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="annee" class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('finances.common.year') }}</label>
            <input type="number" id="annee" name="annee" value="{{ $annee }}" min="2000" max="2100" class="w-32 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
        </div>
        <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">{{ __('finances.common.show') }}</button>
    </form>
</div>

<div class="adventiste-card-pro-static overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[1500px]">
            <thead>
                <tr class="border-b-2 border-slate-300 dark:border-slate-600 bg-slate-100/90 dark:bg-slate-700/80">
                    <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-700 dark:text-slate-200">{{ __('finances.synthese_annuelle.region_congo') }}</th>
                    @foreach ($nomsMois as $nomMois)
                    <th class="px-3 py-3 text-right text-xs font-bold uppercase text-slate-700 dark:text-slate-200">{{ $nomMois }}-{{ substr((string) $annee, -2) }}</th>
                    @endforeach
                    <th class="px-3 py-3 text-right text-xs font-bold uppercase text-slate-700 dark:text-slate-200">{{ __('finances.common.total') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold">
                    <td colspan="14" class="px-3 py-2">{{ __('finances.synthese_annuelle.section_dimes_offrandes') }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_dimes') }}</td>
                    @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['dimes'][$m]) }}</td>@endforeach
                    <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmt($sum($synthese['dimes'])) }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_offrandes') }}</td>
                    @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['offrandes'][$m]) }}</td>@endforeach
                    <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmt($sum($synthese['offrandes'])) }}</td>
                </tr>
                <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold">
                    <td class="px-3 py-2.5">{{ __('finances.synthese_annuelle.row_total_di_off') }}</td>
                    @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['total_dimes_offrandes'][$m]) }}</td>@endforeach
                    <td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($sum($synthese['total_dimes_offrandes'])) }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_ratio') }}</td>
                    @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmtPct($synthese['ratio_offrandes_dimes'][$m]) }}</td>@endforeach
                    @php
                        $sumDimes = $sum($synthese['dimes']);
                        $sumOff = $sum($synthese['offrandes']);
                        $ratioTotal = $sumDimes > 0 ? ($sumOff / $sumDimes) * 100 : 0;
                    @endphp
                    <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmtPct($ratioTotal) }}</td>
                </tr>

                <tr class="bg-slate-100/90 dark:bg-slate-700/70 font-semibold">
                    <td colspan="14" class="px-3 py-2">{{ __('finances.synthese_annuelle.section_revenus') }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_revenus_dimes') }}</td>
                    @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['revenus_dimes'][$m]) }}</td>@endforeach
                    <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmt($sum($synthese['revenus_dimes'])) }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_revenus_offrandes') }}</td>
                    @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['revenus_offrandes'][$m]) }}</td>@endforeach
                    <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmt($sum($synthese['revenus_offrandes'])) }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_autres_offr') }}</td>
                    @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['autres_offrandes'][$m]) }}</td>@endforeach
                    <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmt($sum($synthese['autres_offrandes'])) }}</td>
                </tr>
                <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold">
                    <td class="px-3 py-2.5">{{ __('finances.synthese_annuelle.row_total_revenus') }}</td>
                    @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['total_revenus'][$m]) }}</td>@endforeach
                    <td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($sum($synthese['total_revenus'])) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="adventiste-card-pro-static overflow-hidden mt-6">
    @if ($peutSaisir)
    <form method="post" action="{{ route('finances.synthese-annuelle-mission.update', ['annee' => $annee]) }}" data-offline-queue>
        @csrf
        @method('PUT')
    @endif
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[1700px]">
                <thead>
                    <tr class="border-b-2 border-slate-300 dark:border-slate-600 bg-slate-100/90 dark:bg-slate-700/80">
                        <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-700 dark:text-slate-200">{{ __('finances.synthese_annuelle.section_transfert') }}</th>
                        @foreach ($nomsMois as $nomMois)
                        <th class="px-3 py-3 text-right text-xs font-bold uppercase text-slate-700 dark:text-slate-200">{{ $nomMois }}-{{ substr((string) $annee, -2) }}</th>
                        @endforeach
                        <th class="px-3 py-3 text-right text-xs font-bold uppercase text-slate-700 dark:text-slate-200">{{ __('finances.common.total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                    <tr>
                        <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_pct_dimes') }}</td>
                        @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['transfert_dimes'][$m]) }}</td>@endforeach
                        <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmt($sum($synthese['transfert_dimes'])) }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_pct_offr') }}</td>
                        @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['transfert_offrandes'][$m]) }}</td>@endforeach
                        <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmt($sum($synthese['transfert_offrandes'])) }}</td>
                    </tr>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold">
                        <td class="px-3 py-2.5">{{ __('finances.synthese_annuelle.row_total_transfer') }}</td>
                        @foreach ($keysMois as $m)<td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($synthese['total_a_transferer'][$m]) }}</td>@endforeach
                        <td class="px-3 py-2.5 text-right tabular-nums">{{ $fmt($sum($synthese['total_a_transferer'])) }}</td>
                    </tr>

                    <tr class="bg-yellow-100/80 dark:bg-yellow-900/30">
                        <td class="px-3 py-2.5 font-semibold">{{ __('finances.synthese_annuelle.row_transf_bank') }}</td>
                        @foreach ($keysMois as $m)
                        <td class="px-3 py-2.5 text-right">
                            @if ($peutSaisir)
                            <input type="text" inputmode="decimal" name="transferts[{{ $m }}][montant_transfere]" value="{{ old('transferts.'.$m.'.montant_transfere', $synthese['transfert_banque_effectue'][$m]) }}" class="w-28 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1 text-right tabular-nums text-xs">
                            @else
                            <span class="tabular-nums">{{ $fmt($synthese['transfert_banque_effectue'][$m]) }}</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmt($sum($synthese['transfert_banque_effectue'])) }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_diff_transfert') }}</td>
                        @foreach ($keysMois as $m)
                        <td class="px-3 py-2.5 text-right tabular-nums {{ $synthese['difference_transfert'][$m] == 0.0 ? 'text-slate-700 dark:text-slate-300' : 'text-red-700 dark:text-red-300 font-semibold' }}">
                            {{ $fmt($synthese['difference_transfert'][$m]) }}
                        </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-right tabular-nums font-semibold">{{ $fmt($sum($synthese['difference_transfert'])) }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2.5 font-medium">{{ __('finances.synthese_annuelle.row_obs_transferts') }}</td>
                        @foreach ($keysMois as $m)
                        <td class="px-3 py-2.5">
                            @if ($peutSaisir)
                            <input type="text" name="transferts[{{ $m }}][observation]" value="{{ old('transferts.'.$m.'.observation', $synthese['observations_transferts'][$m]) }}" class="w-36 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1 text-xs">
                            @else
                            <span class="text-xs text-slate-600 dark:text-slate-300">{{ $synthese['observations_transferts'][$m] ?: '—' }}</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-right text-slate-500">—</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if ($peutSaisir)
        <div class="p-4 border-t border-slate-200 dark:border-slate-700 flex gap-3">
            <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">{{ __('finances.synthese_annuelle.save_transferts') }}</button>
        </div>
        @endif
    @if ($peutSaisir)
    </form>
    @endif
</div>
@endsection
