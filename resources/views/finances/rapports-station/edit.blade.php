@extends('layouts.app')

@section('page-title', 'Éditer rapport — ' . $rapport->mois . '/' . $rapport->annee)

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ $rapport->mission?->nom }}</span>
@endsection

@section('content')
<div class="adventiste-card-pro-static max-w-4xl p-6 sm:p-8">
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
        Complétez les montants mensuels pour chaque ligne de ventilation. Les cumuls se calculent automatiquement.
    </p>

    <form method="post" action="{{ route('finances.rapports-station.update', $rapport) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Ventilation trésorerie</h2>
            <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Code</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">%</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Mois (FCFA)</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Cumul</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                        @forelse ($rapport->lignes->sortBy('ordre_tri') as $ligne)
                        <tr class="hover:bg-emerald-50/50 dark:hover:bg-slate-700/40">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-slate-600 dark:text-slate-400">{{ $ligne->code_ligne }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400">
                                @if ($ligne->pourcentage)
                                {{ number_format($ligne->pourcentage, 2) }}%
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="lignes[{{ $ligne->id }}][montant_mois]" step="0.01" min="0" value="{{ old('lignes.' . $ligne->id . '.montant_mois', $ligne->montant_mois) }}" class="w-full px-2 py-1 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
                                <input type="hidden" name="lignes[{{ $ligne->id }}][montant_mois_saisi_manuel]" value="0" />
                                <input type="checkbox" name="lignes[{{ $ligne->id }}][montant_mois_saisi_manuel]" value="1" class="mt-2" @checked(old('lignes.' . $ligne->id . '.montant_mois_saisi_manuel', $ligne->montant_mois_saisi_manuel)) />
                            </td>
                            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400">
                                <span class="font-semibold">{{ number_format($ligne->montant_cumule, 2, ',', ' ') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                                Aucune ligne de ventilation.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Autres dîmes</h2>
            <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Catégorie</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Montant (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                        @forelse ($rapport->lignesAutresDimes as $autre)
                        <tr class="hover:bg-emerald-50/50 dark:hover:bg-slate-700/40">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-slate-600 dark:text-slate-400">{{ $autre->code_categorie }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="autres_dimes[{{ $autre->id }}][montant_mois]" step="0.01" min="0" value="{{ old('autres_dimes.' . $autre->id . '.montant_mois', $autre->montant_mois) }}" class="w-full px-2 py-1 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                                Aucune autre dîme enregistrée.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-200/80 dark:border-slate-600/60">
            <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
            <a href="{{ route('finances.rapports-station.show', $rapport) }}" class="adventiste-btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
