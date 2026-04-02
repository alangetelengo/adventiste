@extends('layouts.app')

@section('page-title', 'Rapport de station — ' . $rapport->mois . '/' . $rapport->annee)

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ $rapport->mission?->nom }}</span>
@endsection

@section('btn-create')
@can('update', $rapport)
<a href="{{ route('finances.rapports-station.edit', $rapport) }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
    Éditer
</a>
@endcan
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        <div class="adventiste-card-pro-static p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Ventilation trésorerie</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-slate-400">Code/Item</th>
                            <th class="px-4 py-2 text-right font-semibold text-slate-600 dark:text-slate-400">%</th>
                            <th class="px-4 py-2 text-right font-semibold text-slate-600 dark:text-slate-400">Mois</th>
                            <th class="px-4 py-2 text-right font-semibold text-slate-600 dark:text-slate-400">Cumul</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                        @forelse ($rapport->lignes as $ligne)
                        <tr class="hover:bg-emerald-50/50 dark:hover:bg-slate-700/40">
                            <td class="px-4 py-3 font-mono text-xs">{{ $ligne->code_ligne }}</td>
                            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400">
                                @if ($ligne->pourcentage)
                                {{ number_format($ligne->pourcentage, 2) }}%
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format($ligne->montant_mois, 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format($ligne->montant_cumule, 2, ',', ' ') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400 text-sm">
                                Aucune ligne de ventilation.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="adventiste-card-pro-static p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Autres dîmes</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-slate-400">Catégorie</th>
                            <th class="px-4 py-2 text-right font-semibold text-slate-600 dark:text-slate-400">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                        @forelse ($rapport->lignesAutresDimes as $autre)
                        <tr class="hover:bg-emerald-50/50 dark:hover:bg-slate-700/40">
                            <td class="px-4 py-3 font-mono text-xs">{{ $autre->code_categorie }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format($autre->montant_mois, 2, ',', ' ') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400 text-sm">
                                Aucune autre dîme enregistrée.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="adventiste-card-pro-static p-6 sm:p-8 h-fit">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Infos</h2>
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="text-slate-600 dark:text-slate-400 font-medium">Période</dt>
                <dd class="text-slate-900 dark:text-white font-semibold mt-1">{{ $rapport->mois }}/{{ $rapport->annee }}</dd>
            </div>
            <div>
                <dt class="text-slate-600 dark:text-slate-400 font-medium">Mission</dt>
                <dd class="text-slate-900 dark:text-white font-semibold mt-1">{{ $rapport->mission?->nom ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-600 dark:text-slate-400 font-medium">Dernier remplissage</dt>
                <dd class="text-slate-900 dark:text-white font-semibold mt-1">
                    @if ($rapport->dernier_remplissage_auto_le)
                    {{ $rapport->dernier_remplissage_auto_le->format('d M Y à H:i') }}
                    @else
                    —
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>

<div class="mt-6 text-right">
    <a href="{{ route('finances.rapports-station.index') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-sm">
        Retour aux rapports
    </a>
</div>
@endsection
