@extends('layouts.app')

@section('page-title', 'Rapports mensuels')

@section('page-title-info')
    @if ($egliseFiltre)
        {{ $egliseFiltre->nom }} ({{ $egliseFiltre->code_unique }})
    @else
        Toutes les églises de la mission
    @endif
@endsection

@section('btn-create')
    @can('create', App\Models\RapportMensuelEglise::class)
        <a href="{{ route('finances.rapports-mensuels.create') }}" class="adventiste-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Nouveau rapport
        </a>
    @endcan
@endsection

@php
    $nomsMois = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
@endphp

@section('content')
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
        <form method="get" action="{{ route('finances.rapports-mensuels.index') }}" class="px-6 py-4 flex flex-wrap items-end gap-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
            <div class="min-w-48">
                <label for="f_etat" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Transmission mission</label>
                <select name="etat_transmission" id="f_etat" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                    <option value="">Tous</option>
                    @foreach ($etatsTransmission as $k => $v)
                        <option value="{{ $k }}" @selected(request('etat_transmission') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="adventiste-btn-primary">Filtrer</button>
                @if (request()->has('etat_transmission') && request('etat_transmission') !== null && request('etat_transmission') !== '')
                    <a href="{{ route('finances.rapports-mensuels.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
                @endif
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Période</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Église</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Dîmes (mois)</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Verrouillage</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Transmission</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                    @forelse ($rapports as $rapport)
                        <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium whitespace-nowrap">
                                {{ $nomsMois[(int) $rapport->mois] ?? $rapport->mois }} {{ $rapport->annee }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                {{ $rapport->egliseLocale->nom }}
                            </td>
                            <td class="px-6 py-4 text-right tabular-nums text-slate-700 dark:text-slate-200 font-medium">
                                {{ number_format((float) $rapport->total_dimes_mois, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4">
                                @if ($rapport->verrouille_le)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-200 dark:bg-slate-600/80 text-slate-800 dark:text-slate-100 border border-slate-300/50 dark:border-slate-500/40">Verrouillé</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-100 border border-amber-200/50 dark:border-amber-800/40">Modifiable</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php $etat = $rapport->etat_transmission ?? \App\Models\RapportMensuelEglise::ETAT_BROUILLON; @endphp
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-200 dark:bg-slate-600/80 text-slate-800 dark:text-slate-100">
                                    {{ $etatsTransmission[$etat] ?? $etat }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions sur le rapport">
                                    <a href="{{ route('finances.rapports-mensuels.show', $rapport) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800/90 px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#00b464]/25 no-underline" title="Voir le rapport">
                                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Voir</span>
                                    </a>
                                    @can('update', $rapport)
                                        <a href="{{ route('finances.rapports-mensuels.edit', $rapport) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 px-2.5 py-1.5 text-xs font-semibold text-[#00a055] dark:text-emerald-300 shadow-sm hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#00b464]/30 no-underline" title="Signatures et verrouillage">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span>Signatures</span>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 text-sm">
                                Aucun rapport mensuel pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rapports->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40">
                {{ $rapports->links() }}
            </div>
        @endif
    </div>
@endsection
