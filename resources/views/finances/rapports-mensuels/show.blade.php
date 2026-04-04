@extends('layouts.app')

@section('page-title', 'Rapport mensuel')

@section('page-title-info')
    @php
        $nomsMois = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    @endphp
    {{ $nomsMois[(int) $rapport->mois] ?? $rapport->mois }} {{ $rapport->annee }} — {{ $rapport->egliseLocale->nom }}
@endsection

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        @can('update', $rapport)
            @if ($rapport->verrouille_le === null)
                <form method="post" action="{{ route('finances.rapports-mensuels.regenerer', $rapport) }}" class="inline" data-offline-queue>
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border-2 border-[#00b464]/40 text-[#00a055] dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-all text-sm">
                        Régénérer depuis les récaps
                    </button>
                </form>
            @endif
        @endcan
        @can('soumettre', $rapport)
            <form method="post" action="{{ route('finances.rapports-mensuels.soumettre', $rapport) }}" class="inline" data-offline-queue>
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#00b464] text-white font-semibold hover:bg-[#00a055] shadow-sm hover:shadow transition-all duration-200 text-sm">
                    Soumettre à la mission
                </button>
            </form>
        @endcan
        <a href="{{ route('finances.rapports-mensuels.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border-2 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all text-sm no-underline">
            Liste
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @php
            $labelsTransmission = \App\Models\RapportMensuelEglise::labelsEtatsTransmission();
            $etatTransmission = $rapport->etat_transmission ?? \App\Models\RapportMensuelEglise::ETAT_BROUILLON;
        @endphp
        <div class="flex flex-wrap gap-3">
            @can('update', $rapport)
                @if ($rapport->verrouille_le === null)
                    <a href="{{ route('finances.rapports-mensuels.edit', $rapport) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#00b464] text-white font-semibold hover:bg-[#00a055] shadow-sm hover:shadow transition-all duration-200 text-sm no-underline">
                        Signatures & verrouillage
                    </a>
                @endif
            @endcan
            @can('delete', $rapport)
                <form method="post" action="{{ route('finances.rapports-mensuels.destroy', $rapport) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border-2 border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 font-semibold hover:bg-red-50 dark:hover:bg-red-950/30 transition-all text-sm" onclick="flashAlert('Supprimer définitivement ce rapport ? Cette action est irréversible.', this.closest('form'), { icon: '🗑️', danger: true, confirmText: 'Supprimer' })">
                        Supprimer
                    </button>
                </form>
            @endcan
        </div>

        @can('reviewMission', $rapport)
            <div class="adventiste-card-pro-static p-6">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Validation mission</h2>
                <div class="flex flex-wrap gap-3">
                    <form method="post" action="{{ route('finances.rapports-mensuels.valider-mission', $rapport) }}" class="inline-flex items-center gap-2" data-offline-queue>
                        @csrf
                        <input type="text" name="mission_commentaire" placeholder="Commentaire (optionnel)" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-700 text-white text-sm font-semibold hover:bg-emerald-800">Valider</button>
                    </form>
                    <form method="post" action="{{ route('finances.rapports-mensuels.refuser-mission', $rapport) }}" class="inline-flex items-center gap-2" data-offline-queue>
                        @csrf
                        <input type="text" name="mission_commentaire" required placeholder="Motif du refus (obligatoire)" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-red-300 text-red-700 dark:text-red-400 text-sm font-semibold hover:bg-red-50 dark:hover:bg-red-900/20">Refuser</button>
                    </form>
                </div>
            </div>
        @endcan

        <div class="adventiste-card-pro-static p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Transmission vers la mission</h2>
            <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-slate-500">État</dt>
                    <dd class="font-medium mt-1">{{ $labelsTransmission[$etatTransmission] ?? $etatTransmission }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Soumis le</dt>
                    <dd class="font-medium mt-1">{{ $rapport->soumis_le?->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Soumis par</dt>
                    <dd class="font-medium mt-1">{{ $rapport->soumisPar?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Revu mission le</dt>
                    <dd class="font-medium mt-1">{{ $rapport->mission_revu_le?->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">Commentaire mission</dt>
                    <dd class="font-medium mt-1 whitespace-pre-wrap">{{ $rapport->mission_commentaire ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="adventiste-card-pro-static p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Récapitulatif (mois / précédent / cumulé)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[760px]">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-slate-700/80 border-b border-slate-200 dark:border-slate-600">
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Rubrique</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Mois</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Précédent</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Cumulé</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr>
                            <td class="px-4 py-2.5">Dîmes</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) ($resumeComparatif['dimes']['mois'] ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) ($resumeComparatif['dimes']['precedent'] ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2.5 text-right tabular-nums font-semibold">{{ number_format((float) ($resumeComparatif['dimes']['cumule'] ?? 0), 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5">Moitié offrandes (mission)</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) ($resumeComparatif['moitie_offrandes']['mois'] ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) ($resumeComparatif['moitie_offrandes']['precedent'] ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2.5 text-right tabular-nums font-semibold">{{ number_format((float) ($resumeComparatif['moitie_offrandes']['cumule'] ?? 0), 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5">Autres offrandes mission</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) ($resumeComparatif['autres_offrandes']['mois'] ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) ($resumeComparatif['autres_offrandes']['precedent'] ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2.5 text-right tabular-nums font-semibold">{{ number_format((float) ($resumeComparatif['autres_offrandes']['cumule'] ?? 0), 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold">
                            <td class="px-4 py-2.5">Total à transférer mission</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) ($resumeComparatif['transferer_mission']['mois'] ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) ($resumeComparatif['transferer_mission']['precedent'] ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) ($resumeComparatif['transferer_mission']['cumule'] ?? 0), 0, ',', ' ') }} FCFA</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @if ($rapport->regenere_le)
                <p class="mt-4 text-xs text-slate-500">Dernière régénération : {{ $rapport->regenere_le->translatedFormat('d M Y à H:i') }}</p>
            @endif
            @if ($rapport->verrouille_le)
                <p class="mt-2 text-xs text-amber-700 dark:text-amber-300">Verrouillé le {{ $rapport->verrouille_le->translatedFormat('d M Y à H:i') }}</p>
            @endif
        </div>

        @if ($rapport->lignesSabbat->isNotEmpty())
            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Par sabbat</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-left text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="px-4 py-3 font-medium">#</th>
                                <th class="px-4 py-3 font-medium">Date</th>
                                <th class="px-4 py-3 font-medium text-right">Dîmes</th>
                                <th class="px-4 py-3 font-medium text-right">½ offrandes</th>
                                <th class="px-4 py-3 font-medium text-right">À transférer</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($rapport->lignesSabbat as $ligne)
                                <tr>
                                    <td class="px-4 py-2">{{ $ligne->indice_sabbat_dans_mois }}</td>
                                    <td class="px-4 py-2">{{ $ligne->date_sabbat?->translatedFormat('d M Y') ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $ligne->total_dimes, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $ligne->moitie_offrandes, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $ligne->total_transferer_mission, 0, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($rapport->lignesSynthese->isNotEmpty())
            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Grille de synthèse</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-left text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="px-4 py-3 font-medium">Sabbat</th>
                                <th class="px-4 py-3 font-medium text-right">Dîmes</th>
                                <th class="px-4 py-3 font-medium text-right">École du sabbat</th>
                                <th class="px-4 py-3 font-medium text-right">Budget local</th>
                                <th class="px-4 py-3 font-medium text-right">Fonds mission</th>
                                <th class="px-4 py-3 font-medium text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($rapport->lignesSynthese->sortBy('indice_sabbat') as $ls)
                                <tr>
                                    <td class="px-4 py-2">
                                        @if ($ls->indice_sabbat === 0)
                                            <strong>Total général</strong>
                                        @else
                                            {{ $ls->indice_sabbat }} — {{ $ls->date_sabbat?->translatedFormat('d M') ?? '—' }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $ls->dimes, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $ls->offrande_ecole_sabbat, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $ls->budget_eglise_locale, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $ls->fonds_missionnaires, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums font-medium">{{ number_format((float) $ls->montant_total, 0, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
