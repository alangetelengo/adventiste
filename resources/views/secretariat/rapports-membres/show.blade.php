@extends('layouts.app')

@section('page-title', 'Rapport membres')

@section('page-title-info')
    @php
        $nomsMois = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    @endphp
    {{ $typesPeriode[$rapport->type_periode] ?? $rapport->type_periode }}
    —
    @if ((int) $rapport->mois > 0)
        {{ $nomsMois[(int) $rapport->mois] ?? $rapport->mois }}
    @else
        Année complète
    @endif
    {{ $rapport->annee }} — {{ $rapport->egliseLocale->nom }}
@endsection

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('secretariat.rapports-membres.impression', $rapport) }}" target="_blank" class="adventiste-btn-secondary">Imprimer / PDF</a>
        @can('update', $rapport)
            <a href="{{ route('secretariat.rapports-membres.edit', $rapport) }}" class="adventiste-btn-primary">Modifier</a>
        @endcan
        <a href="{{ route('secretariat.rapports-membres.index') }}" class="adventiste-btn-secondary">Liste</a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap gap-3">
            @can('soumettre', $rapport)
                <form method="post" action="{{ route('secretariat.rapports-membres.soumettre', $rapport) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#00b464] text-white font-semibold hover:bg-[#00a055] shadow-sm hover:shadow transition-all duration-200 text-sm">
                        Envoyer à la mission
                    </button>
                </form>
            @endcan

            @can('review', $rapport)
                <form method="post" action="{{ route('secretariat.rapports-membres.valider', $rapport) }}" class="inline-flex items-center gap-2">
                    @csrf
                    <input type="text" name="commentaire_mission" placeholder="Commentaire (optionnel)" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-700 text-white text-sm font-semibold hover:bg-emerald-800">Valider</button>
                </form>

                <form method="post" action="{{ route('secretariat.rapports-membres.rejeter', $rapport) }}" class="inline-flex items-center gap-2">
                    @csrf
                    <input type="text" name="commentaire_mission" required placeholder="Motif du rejet (obligatoire)" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-red-300 text-red-700 dark:text-red-400 text-sm font-semibold hover:bg-red-50 dark:hover:bg-red-900/20">Rejeter</button>
                </form>
            @endcan

            @can('delete', $rapport)
                <form method="post" action="{{ route('secretariat.rapports-membres.destroy', $rapport) }}" class="inline m-0">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="inline-flex items-center rounded-lg border border-red-200 dark:border-red-800/80 bg-red-50/80 dark:bg-red-950/35 px-4 py-2 text-sm font-semibold text-red-700 dark:text-red-300" onclick="flashAlert('Supprimer définitivement ce rapport ?', this.closest('form'), { icon: '🗑️', danger: true, confirmText: 'Supprimer' })">Supprimer</button>
                </form>
            @endcan
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">État de transmission</h2>
            <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-slate-500">État</dt>
                    <dd class="font-medium mt-1">{{ $etats[$rapport->etat] ?? $rapport->etat }}</dd>
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
                    <dt class="text-slate-500">Revu le</dt>
                    <dd class="font-medium mt-1">{{ $rapport->revu_le?->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">Commentaire mission</dt>
                    <dd class="font-medium mt-1 whitespace-pre-wrap">{{ $rapport->commentaire_mission ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Synthèse membres</h2>
            <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-slate-700 pb-2">
                    <dt class="text-slate-500">Total membres</dt>
                    <dd class="font-medium tabular-nums">{{ number_format((int) $rapport->total_membres, 0, ',', ' ') }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-slate-700 pb-2">
                    <dt class="text-slate-500">Baptêmes immersion</dt>
                    <dd class="font-medium tabular-nums">{{ number_format((int) $rapport->total_baptemes_immersion, 0, ',', ' ') }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-slate-700 pb-2">
                    <dt class="text-slate-500">Baptêmes profession de foi</dt>
                    <dd class="font-medium tabular-nums">{{ number_format((int) $rapport->total_baptemes_profession_foi, 0, ',', ' ') }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-slate-700 pb-2">
                    <dt class="text-slate-500">Entrées par transfert</dt>
                    <dd class="font-medium tabular-nums">{{ number_format((int) $rapport->total_entrees_transfert, 0, ',', ' ') }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-slate-700 pb-2">
                    <dt class="text-slate-500">Changements de statut</dt>
                    <dd class="font-medium tabular-nums">{{ number_format((int) $rapport->total_changements_statut, 0, ',', ' ') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Répartition par statut membre</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 text-left text-slate-600 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Statut</th>
                            <th class="px-4 py-3 font-medium text-right">Effectif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse (($rapport->stats_statuts ?? []) as $statut)
                            <tr>
                                <td class="px-4 py-2">{{ $statut['libelle'] ?? '—' }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ number_format((int) ($statut['total'] ?? 0), 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-slate-500">Aucune donnée de statut.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Notes du secrétaire</h2>
            <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $rapport->notes_locales ?? '—' }}</p>
        </div>
    </div>
@endsection
