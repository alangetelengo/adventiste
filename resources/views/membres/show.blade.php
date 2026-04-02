@extends('layouts.app')

@section('page-title', 'Fiche membre')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ $membre->nom }} {{ $membre->prenom }}</span>
@endsection

@section('btn-create')
    @can('update', $membre)
        <a href="{{ route('membres.edit', $membre) }}" class="adventiste-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Modifier
        </a>
    @endcan
@endsection

@section('content')
    @php
        $modesEntree = \App\Models\Membre::labelsModesEntree();
        $typesBaptemeEntree = \App\Models\Membre::labelsTypesBaptemeEntree();
    @endphp
    <div class="max-w-4xl space-y-6">
        <dl class="adventiste-card-pro-static p-6 sm:p-7 grid gap-5 sm:grid-cols-2 text-sm">
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Église locale</dt>
                <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $membre->egliseLocale?->nom ?? '—' }} @if ($membre->egliseLocale)<span class="font-mono text-xs text-slate-500">({{ $membre->egliseLocale->code_unique }})</span>@endif</dd>
            </div>
            @if ($membre->groupeMission)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Groupe mission</dt>
                    <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->groupeMission->nom }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Sexe</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->sexe ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Date de naissance</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->date_naissance?->translatedFormat('d M Y') ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Lieu de naissance</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->lieu_naissance ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Téléphone</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->telephone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Occupation</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->occupation ?? '—' }}</dd>
            </div>
            @if ($membre->adresses)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Adresse(s)</dt>
                    <dd class="mt-1 text-slate-800 dark:text-slate-200 whitespace-pre-wrap">{{ $membre->adresses }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Mode d'entrée</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $modesEntree[$membre->mode_entree] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut membre</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->typeStatut?->libelle ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Type de baptême d'entrée</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $typesBaptemeEntree[$membre->type_bapteme_entree] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Date de baptême</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->date_bapteme?->translatedFormat('d M Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Lieu de baptême</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->lieu_bapteme ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Église d'origine (transfert)</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->recu_dans_eglise_de ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Date de réception (transfert)</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->recu_le?->translatedFormat('d M Y') ?? '—' }}</dd>
            </div>
        </dl>

        @if ($membre->observations)
            <div class="adventiste-card-pro-static p-6 sm:p-7">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-2">Observations</h2>
                <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $membre->observations }}</p>
            </div>
        @endif

        @can('changeStatut', $membre)
            <div class="adventiste-card-pro-static p-6 sm:p-7 space-y-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200">Changer le statut du membre</h2>
                <form method="post" action="{{ route('membres.change-statut', $membre) }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label for="type_statut_membre_id" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Nouveau statut</label>
                        <select name="type_statut_membre_id" id="type_statut_membre_id" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                            @foreach ($typesStatut as $typeStatut)
                                <option value="{{ $typeStatut->id }}" @selected((string) old('type_statut_membre_id', $membre->type_statut_membre_id) === (string) $typeStatut->id)>{{ $typeStatut->libelle }}</option>
                            @endforeach
                        </select>
                        @error('type_statut_membre_id')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="motif" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Motif (optionnel)</label>
                        <textarea name="motif" id="motif" rows="3" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">{{ old('motif') }}</textarea>
                        @error('motif')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="adventiste-btn-primary">Mettre à jour le statut</button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="adventiste-card-pro-static p-6 sm:p-7">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">Historique des statuts</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="py-2 text-left text-xs uppercase tracking-wide text-slate-500">Date</th>
                            <th class="py-2 text-left text-xs uppercase tracking-wide text-slate-500">Statut</th>
                            <th class="py-2 text-left text-xs uppercase tracking-wide text-slate-500">Par</th>
                            <th class="py-2 text-left text-xs uppercase tracking-wide text-slate-500">Motif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($membre->historiqueStatuts as $ligne)
                            <tr>
                                <td class="py-2 text-slate-700 dark:text-slate-200">{{ $ligne->changed_at?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                                <td class="py-2 text-slate-700 dark:text-slate-200">{{ $ligne->typeStatut?->libelle ?? '—' }}</td>
                                <td class="py-2 text-slate-700 dark:text-slate-200">{{ $ligne->changePar?->name ?? 'Système' }}</td>
                                <td class="py-2 text-slate-600 dark:text-slate-300">{{ $ligne->motif ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500">Aucun changement de statut enregistré.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('membres.index') }}" class="adventiste-btn-secondary">Retour à la liste</a>
            @can('delete', $membre)
                <form method="post" action="{{ route('membres.destroy', $membre) }}" class="inline m-0">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="inline-flex items-center rounded-lg border border-red-200 dark:border-red-800/80 bg-red-50/80 dark:bg-red-950/35 px-4 py-2 text-sm font-semibold text-red-700 dark:text-red-300" onclick="flashAlert('Supprimer définitivement cette fiche ?', this.closest('form'), { icon: '🗑️', danger: true, confirmText: 'Supprimer' })">Supprimer</button>
                </form>
            @endcan
        </div>
    </div>
@endsection
