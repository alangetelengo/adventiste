@extends('layouts.app')

@php
    $nomsMois = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
@endphp

@section('page-title', 'Ventilation trésorerie mission')

@section('page-title-info')
    {{ $nomsMois[(int) $mois] ?? $mois }} {{ $annee }}
    @php
        $etat = (string) ($rapport->etat_transmission ?? \App\Models\MissionTresorerieRapportMensuel::ETAT_BROUILLON);
        $labelsEtats = \App\Models\MissionTresorerieRapportMensuel::labelsEtatsTransmission();
    @endphp
    <span class="text-slate-500">— {{ strtolower($labelsEtats[$etat] ?? 'Brouillon') }}</span>
@endsection

@section('btn-create')
    <a href="{{ route('finances.ventilation-tresorerie-mission.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline">
        Liste des périodes
    </a>
    <a href="{{ route('finances.ventilation-tresorerie-mission.export-pdf', ['annee' => $annee, 'mois' => $mois]) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline">
        Export PDF
    </a>
    <a href="{{ route('finances.ventilation-tresorerie-mission.export-excel', ['annee' => $annee, 'mois' => $mois]) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline">
        Export Excel
    </a>
    <a href="{{ route('finances.ventilation-tresorerie-mission.impression', ['annee' => $annee, 'mois' => $mois]) }}" target="_blank" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline">
        Aperçu impression
    </a>
    @can('viewAny', App\Models\MissionTresorerieVentilationLigne::class)
        <a href="{{ route('parametres.tresorerie-ventilation-lignes.edit') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline">
            Lignes du rapport
        </a>
    @endcan
@endsection

@section('content')
    @php
        $etat = (string) ($rapport->etat_transmission ?? \App\Models\MissionTresorerieRapportMensuel::ETAT_BROUILLON);
        $etatClass = match ($etat) {
            \App\Models\MissionTresorerieRapportMensuel::ETAT_SOUMIS => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            \App\Models\MissionTresorerieRapportMensuel::ETAT_VALIDE_MISSION => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            \App\Models\MissionTresorerieRapportMensuel::ETAT_REFUSE_MISSION => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
        };
    @endphp

    <div class="adventiste-card-pro-static mb-5 p-4 flex flex-wrap items-center gap-3">
        <span class="text-sm text-slate-600 dark:text-slate-300">Statut du workflow:</span>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $etatClass }}">
            {{ \App\Models\MissionTresorerieRapportMensuel::labelsEtatsTransmission()[$etat] ?? 'Brouillon' }}
        </span>
        @if (!empty($rapport->mission_commentaire))
            <span class="text-xs text-slate-500">Commentaire mission: {{ $rapport->mission_commentaire }}</span>
        @endif
    </div>

    <div class="mb-6 flex flex-wrap gap-3">
        @can('soumettre', $rapport)
            <form method="post" action="{{ route('finances.ventilation-tresorerie-mission.soumettre', ['annee' => $annee, 'mois' => $mois]) }}" data-offline-queue>
                @csrf
                <button type="submit" class="rounded-lg bg-indigo-700 text-white px-4 py-2 text-sm font-medium hover:bg-indigo-800">
                    Soumettre à validation
                </button>
            </form>
        @endcan

        @can('reviewMission', $rapport)
            <form method="post" action="{{ route('finances.ventilation-tresorerie-mission.valider-mission', ['annee' => $annee, 'mois' => $mois]) }}" class="flex flex-wrap items-center gap-2" data-offline-queue>
                @csrf
                <input type="text" name="mission_commentaire" placeholder="Commentaire (optionnel)" class="rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-xs min-w-48">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white px-4 py-2 text-sm font-medium hover:bg-emerald-800">
                    Valider
                </button>
            </form>

            <form method="post" action="{{ route('finances.ventilation-tresorerie-mission.refuser-mission', ['annee' => $annee, 'mois' => $mois]) }}" class="flex flex-wrap items-center gap-2" data-offline-queue>
                @csrf
                <input type="text" name="mission_commentaire" required placeholder="Commentaire de refus (obligatoire)" class="rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-xs min-w-48">
                <button type="submit" class="rounded-lg bg-rose-700 text-white px-4 py-2 text-sm font-medium hover:bg-rose-800">
                    Refuser
                </button>
            </form>
        @endcan
    </div>

    @can('update', $rapport)
        <div class="mb-6 flex flex-wrap gap-3">
            <span class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-emerald-50/80 dark:bg-emerald-950/40 px-4 py-2 text-sm font-medium text-emerald-900 dark:text-emerald-100">
                Mode simplifié actif: dîmes/offrandes auto depuis les agrégats
            </span>
        </div>
    @endcan

    <div class="adventiste-card-pro-static overflow-hidden mb-8">
        @can('update', $rapport)
            <form method="post" action="{{ route('finances.ventilation-tresorerie-mission.update', ['annee' => $annee, 'mois' => $mois]) }}" class="p-6 sm:p-7 space-y-6" data-offline-queue>
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="dimes_eglises" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Dîmes des églises</label>
                        <input type="text" inputmode="decimal" id="dimes_eglises" name="dimes_eglises" value="{{ old('dimes_eglises', $rapport->dimes_eglises) }}" readonly
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm tabular-nums">
                        <p class="mt-1 text-xs text-slate-500">Auto (agrégats mission)</p>
                    </div>
                    <div>
                        <label for="autres_dimes" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Autres dîmes (ajustement exceptionnel)</label>
                        <input type="text" inputmode="decimal" id="autres_dimes" name="autres_dimes" value="{{ old('autres_dimes', $rapport->autres_dimes) }}" required
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm tabular-nums">
                    </div>
                    <div>
                        <label for="offrandes_mois" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Offrandes du mois</label>
                        <input type="text" inputmode="decimal" id="offrandes_mois" name="offrandes_mois" value="{{ old('offrandes_mois', $rapport->offrandes_mois) }}" readonly
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm tabular-nums">
                        <p class="mt-1 text-xs text-slate-500">Auto (agrégats mission)</p>
                    </div>
                </div>

                <div class="rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-600 px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                    <span class="font-semibold">Total dîmes mois :</span>
                    {{ number_format($rapport->totalDimesMois(), 2, ',', ' ') }}
                    <span class="mx-3 text-slate-400">|</span>
                    <span class="font-semibold">Total recettes (dîmes + offrandes) :</span>
                    {{ number_format($rapport->totalRecettesMois(), 2, ',', ' ') }}
                </div>

                @php
                    $lignesByCode = $lignes->filter(fn($l) => !$l->estTitre() && $l->code)->keyBy('code');
                    $getMois = function (string $code) use ($lignesByCode, $montantsParLigne): float {
                        if (!isset($lignesByCode[$code])) return 0.0;
                        $id = $lignesByCode[$code]->id;
                        return (float) (($montantsParLigne[$id]['mois'] ?? 0));
                    };
                    $getPrev = function (string $code) use ($lignesByCode, $montantsParLigne): float {
                        if (!isset($lignesByCode[$code])) return 0.0;
                        $id = $lignesByCode[$code]->id;
                        return (float) (($montantsParLigne[$id]['precedent'] ?? 0));
                    };
                    $getCumule = function (string $code) use ($lignesByCode, $montantsParLigne): float {
                        if (!isset($lignesByCode[$code])) return 0.0;
                        $id = $lignesByCode[$code]->id;
                        return (float) (($montantsParLigne[$id]['cumule'] ?? 0));
                    };
                    $pct = function (string $code) use ($lignesByCode): string {
                        if (!isset($lignesByCode[$code])) return '';
                        $p = (float) ($lignesByCode[$code]->pourcentage ?? 0);
                        if ($p <= 0) return '';
                        return rtrim(rtrim(number_format($p, 4, ',', ' '), '0'), ',').'%';
                    };
                @endphp

                <div class="adventiste-card-pro-static overflow-x-auto">
                    <table class="w-full text-sm min-w-[840px]">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-700/80 border-b border-slate-200 dark:border-slate-600">
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Désignation</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">%</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Total du mois</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Total précédent</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Total cumulé</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr class="bg-slate-200/60 dark:bg-slate-700/50 font-semibold">
                                <td class="px-4 py-2.5">DIMES DES EGLISES</td>
                                <td class="px-4 py-2.5 text-right"> </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['dimes_eglises']['mois'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['dimes_eglises']['precedent'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['dimes_eglises']['cumule'] ?? 0, 2, ',', ' ') }}</td>
                            </tr>
                            <tr class="bg-slate-200/60 dark:bg-slate-700/50">
                                <td class="px-4 py-2.5">AUTRES DÎMES</td>
                                <td class="px-4 py-2.5 text-right"> </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['autres_dimes']['mois'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['autres_dimes']['precedent'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['autres_dimes']['cumule'] ?? 0, 2, ',', ' ') }}</td>
                            </tr>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold">
                                <td class="px-4 py-2.5">TOTAL RECETTES DÎMES DU MOIS</td>
                                <td class="px-4 py-2.5 text-right"> </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['total_dimes']['mois'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['total_dimes']['precedent'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['total_dimes']['cumule'] ?? 0, 2, ',', ' ') }}</td>
                            </tr>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold">
                                <td class="px-4 py-2.5">TOTAL OFFRANDES DU MOIS</td>
                                <td class="px-4 py-2.5 text-right"> </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['offrandes_mois']['mois'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['offrandes_mois']['precedent'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['offrandes_mois']['cumule'] ?? 0, 2, ',', ' ') }}</td>
                            </tr>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold">
                                <td class="px-4 py-2.5">TOTAL REVENUS DU MOIS</td>
                                <td class="px-4 py-2.5 text-right"> </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['total_recettes']['mois'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['total_recettes']['precedent'] ?? 0, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['total_recettes']['cumule'] ?? 0, 2, ',', ' ') }}</td>
                            </tr>

                            <tr class="bg-slate-200/60 dark:bg-slate-700/50"><td class="px-4 py-2.5 font-semibold">DIME DE LA DIME (UNION)</td><td class="px-4 py-2.5 text-right">{{ $pct('dime_union') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('dime_union'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('dime_union'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('dime_union'), 2, ',', ' ') }}</td></tr>
                            <tr class="bg-slate-200/60 dark:bg-slate-700/50"><td class="px-4 py-2.5 font-semibold">POURCENTAGE DE LA DIME</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5"></td><td class="px-4 py-2.5"></td><td class="px-4 py-2.5"></td></tr>
                            <tr><td class="px-4 py-2.5">FONDS CONF. GENERALE</td><td class="px-4 py-2.5 text-right">{{ $pct('fonds_cg') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('fonds_cg'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('fonds_cg'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('fonds_cg'), 2, ',', ' ') }}</td></tr>
                            <tr><td class="px-4 py-2.5">FONDS INSTITUTIONS DAO</td><td class="px-4 py-2.5 text-right">{{ $pct('fonds_dao_inst') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('fonds_dao_inst'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('fonds_dao_inst'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('fonds_dao_inst'), 2, ',', ' ') }}</td></tr>
                            <tr><td class="px-4 py-2.5">FONDS DE RETRAITES DAO</td><td class="px-4 py-2.5 text-right">{{ $pct('fonds_retraites') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('fonds_retraites'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('fonds_retraites'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('fonds_retraites'), 2, ',', ' ') }}</td></tr>
                            <tr><td class="px-4 py-2.5">FONDS DIME PARTAGEE DAO</td><td class="px-4 py-2.5 text-right">{{ $pct('fonds_dime_partagee') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('fonds_dime_partagee'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('fonds_dime_partagee'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('fonds_dime_partagee'), 2, ',', ' ') }}</td></tr>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold"><td class="px-4 py-2.5">TOTAL POURCENTAGE DE DIME &gt;&gt;&gt;&gt;&gt;&gt;&gt;</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('total_pct_dime'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('total_pct_dime'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('total_pct_dime'), 2, ',', ' ') }}</td></tr>

                            <tr class="bg-slate-200/60 dark:bg-slate-700/50"><td class="px-4 py-2.5 font-semibold">REPARTITION OFFRANDE</td><td class="px-4 py-2.5 text-right"></td><td></td><td></td><td></td></tr>
                            <tr class="bg-slate-200/60 dark:bg-slate-700/50"><td class="px-4 py-2.5 font-semibold">CONF.GENERALE / DIVISION</td><td class="px-4 py-2.5 text-right"></td><td></td><td></td><td></td></tr>
                            <tr><td class="px-4 py-2.5">FONDS CHAMPS MONDIALE - CG</td><td class="px-4 py-2.5 text-right">{{ $pct('off_cg') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('off_cg'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('off_cg'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('off_cg'), 2, ',', ' ') }}</td></tr>
                            <tr><td class="px-4 py-2.5">FONDS OFFRANDE - DAO</td><td class="px-4 py-2.5 text-right">{{ $pct('off_dao') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('off_dao'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('off_dao'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('off_dao'), 2, ',', ' ') }}</td></tr>
                            <tr class="bg-slate-200/60 dark:bg-slate-700/50"><td class="px-4 py-2.5 font-semibold">UNION MISSION DE L'AFRIQUE CENTRALE</td><td class="px-4 py-2.5 text-right"></td><td></td><td></td><td></td></tr>
                            <tr><td class="px-4 py-2.5">FONDS OFFRANDE UMAC</td><td class="px-4 py-2.5 text-right">{{ $pct('off_umac') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('off_umac'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('off_umac'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('off_umac'), 2, ',', ' ') }}</td></tr>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold"><td class="px-4 py-2.5">TOTAL OFFRANDE (CG, DAO &amp; UMAC) &gt;&gt;&gt;&gt;&gt;&gt;&gt;</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('total_off_haut'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('total_off_haut'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('total_off_haut'), 2, ',', ' ') }}</td></tr>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold"><td class="px-4 py-2.5">TOTAL RAPPORT (GC-DAO-UMAC)</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('total_rapport'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('total_rapport'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('total_rapport'), 2, ',', ' ') }}</td></tr>
                            <tr class="bg-slate-200/60 dark:bg-slate-700/50"><td class="px-4 py-2.5 font-semibold">MISSION / FEDERATION</td><td class="px-4 py-2.5 text-right"></td><td></td><td></td><td></td></tr>
                            <tr><td class="px-4 py-2.5">REPARTITION OFFRANDE</td><td class="px-4 py-2.5 text-right">{{ $pct('off_mission') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('off_mission'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('off_mission'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('off_mission'), 2, ',', ' ') }}</td></tr>
                            <tr><td class="px-4 py-2.5">OFFRANDE SPEC./PROJET</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td></tr>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold"><td class="px-4 py-2.5">TOTAL FONDS MISSION</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('off_mission'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('off_mission'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('off_mission'), 2, ',', ' ') }}</td></tr>
                            <tr class="bg-slate-200/60 dark:bg-slate-700/50"><td class="px-4 py-2.5 font-semibold">EGLISE LOCALE</td><td class="px-4 py-2.5 text-right"></td><td></td><td></td><td></td></tr>
                            <tr><td class="px-4 py-2.5">REPARTITION OFFRANDE</td><td class="px-4 py-2.5 text-right">{{ $pct('off_locale') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('off_locale'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('off_locale'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('off_locale'), 2, ',', ' ') }}</td></tr>
                            <tr><td class="px-4 py-2.5">OFFRANDE SPEC./PROJET</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td></tr>
                            <tr><td class="px-4 py-2.5">FONDS CONSTRUCTION EGLISE LOCALE</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td></tr>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold"><td class="px-4 py-2.5">TOTAL FONDS EGLISE LOCALE</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getMois('off_locale'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getPrev('off_locale'), 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($getCumule('off_locale'), 2, ',', ' ') }}</td></tr>
                            <tr class="bg-slate-200/60 dark:bg-slate-700/50"><td class="px-4 py-2.5 font-semibold">AUTRES DÎMES</td><td class="px-4 py-2.5 text-right"></td><td></td><td></td><td></td></tr>
                            <tr><td class="px-4 py-2.5">DIMES OUVRIERS DE BUREAU</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['autres_dimes']['mois'] ?? 0, 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['autres_dimes']['precedent'] ?? 0, 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['autres_dimes']['cumule'] ?? 0, 2, ',', ' ') }}</td></tr>
                            <tr><td class="px-4 py-2.5">DIMES OUVRIERS GOC ET GOB</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td></tr>
                            <tr><td class="px-4 py-2.5">DIMES PIONNIERS MISSI. GLOB.</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td></tr>
                            <tr><td class="px-4 py-2.5">DIMES RE &amp; LIBRAIRIE</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td></tr>
                            <tr><td class="px-4 py-2.5">DIMES SPECIALES</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td></tr>
                            <tr><td class="px-4 py-2.5">DIMES ECOLES &amp; COLLEGES</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td><td class="px-4 py-2.5 text-right tabular-nums">-</td></tr>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/30 font-semibold"><td class="px-4 py-2.5">TOTAL AUTRES DIMES &gt;&gt;&gt;&gt;&gt;&gt;&gt;</td><td class="px-4 py-2.5 text-right"></td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['autres_dimes']['mois'] ?? 0, 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['autres_dimes']['precedent'] ?? 0, 2, ',', ' ') }}</td><td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($resumeTop['autres_dimes']['cumule'] ?? 0, 2, ',', ' ') }}</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
                        Enregistrer le rapport
                    </button>
                    <a href="{{ route('finances.ventilation-tresorerie-mission.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline inline-flex items-center">
                        Annuler
                    </a>
                </div>
            </form>
        @else
            <div class="p-6 sm:p-7 space-y-6">
                <p class="text-sm text-slate-600 dark:text-slate-400">Vous pouvez consulter ce rapport. La saisie est réservée au trésorier de mission (ou administrateur).</p>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-600 px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                    <span class="font-semibold">Total dîmes mois :</span>
                    {{ number_format($rapport->totalDimesMois(), 2, ',', ' ') }}
                    <span class="mx-3 text-slate-400">|</span>
                    <span class="font-semibold">Total recettes :</span>
                    {{ number_format($rapport->totalRecettesMois(), 2, ',', ' ') }}
                </div>
                <div class="overflow-x-auto -mx-6 sm:mx-0">
                    <table class="w-full text-sm min-w-[640px]">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-700/80 border-b border-slate-200 dark:border-slate-600">
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Ligne</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">%</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Mois</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Précédent</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Cumulé</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($lignes as $ligne)
                                @if ($ligne->estTitre())
                                    <tr class="bg-slate-200/60 dark:bg-slate-700/50">
                                        <td colspan="5" class="px-4 py-2 font-semibold text-slate-800 dark:text-slate-100">{{ $ligne->designation }}</td>
                                    </tr>
                                @else
                                    @php $m = $montantsParLigne[$ligne->id] ?? ['precedent' => 0, 'mois' => 0, 'cumule' => 0]; @endphp
                                    <tr>
                                        <td class="px-4 py-2.5 text-slate-800 dark:text-slate-100">{{ $ligne->designation }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-slate-600 dark:text-slate-400">
                                            @if ($ligne->kind === \App\Models\MissionTresorerieVentilationLigne::KIND_POURCENTAGE_DIMES)
                                                {{ rtrim(rtrim(number_format((float) $ligne->pourcentage, 4, ',', ' '), '0'), ',') }}&nbsp;% dîmes
                                            @elseif ($ligne->kind === \App\Models\MissionTresorerieVentilationLigne::KIND_POURCENTAGE_OFFRANDES)
                                                {{ rtrim(rtrim(number_format((float) $ligne->pourcentage, 4, ',', ' '), '0'), ',') }}&nbsp;% offrandes
                                            @elseif ($ligne->kind === \App\Models\MissionTresorerieVentilationLigne::KIND_SOMME_CODES)
                                                Σ
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right tabular-nums font-medium">{{ number_format($m['mois'], 2, ',', ' ') }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-slate-600 dark:text-slate-400">{{ number_format($m['precedent'], 2, ',', ' ') }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums font-semibold text-emerald-800 dark:text-emerald-300">{{ number_format($m['cumule'], 2, ',', ' ') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endcan
    </div>
@endsection
