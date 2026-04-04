@extends('layouts.app')

@section('page-title', 'Membres')

@section('page-title-info')
@if (auth()->user()->eglise_locale_id)
<span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->egliseLocale?->nom }}</span>
@else
<span class="text-slate-600 dark:text-slate-400">Annuaire des membres rattachés aux églises de la mission</span>
@endif
@endsection

@section('btn-create')
@can('create', App\Models\Membre::class)
<div class="flex flex-wrap items-center gap-2">
<a href="{{ route('membres.create') }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouveau membre
</a>
</div>
@endcan
@endsection

@section('content')
<div class="adventiste-card-pro-static overflow-hidden mb-6">
    <form method="get" action="{{ route('membres.index') }}" class="px-6 py-4 flex flex-wrap items-end gap-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
        @if ($eglisesFiltre !== null && $eglisesFiltre->isNotEmpty())
        <div class="min-w-48">
            <label for="f_eglise" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Église</label>
            <select name="eglise_locale_id" id="f_eglise" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Toutes</option>
                @foreach ($eglisesFiltre as $e)
                <option value="{{ $e->id }}" @selected((string) request('eglise_locale_id')===(string) $e->id)>{{ $e->nom }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="min-w-48">
            <label for="f_mode" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Entrée</label>
            <select name="mode_entree" id="f_mode" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Tous</option>
                @foreach ($modesEntree as $key => $label)
                <option value="{{ $key }}" @selected(request('mode_entree') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-48">
            <label for="f_statut" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Statut</label>
            <select name="type_statut_membre_id" id="f_statut" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Tous</option>
                @foreach ($typesStatut as $typeStatut)
                <option value="{{ $typeStatut->id }}" @selected((string) request('type_statut_membre_id') === (string) $typeStatut->id)>{{ $typeStatut->libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-48 flex-1 max-w-md">
            <label for="f_q" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Recherche</label>
            <input type="search" name="q" id="f_q" value="{{ request('q') }}" placeholder="Nom, prénom, téléphone…" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="adventiste-btn-primary">Filtrer</button>
            @if (request()->hasAny(['eglise_locale_id', 'mode_entree', 'type_statut_membre_id', 'q']))
            <a href="{{ route('membres.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            @endif
        </div>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Nom</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">Date d'entrée</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">Entrée</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden lg:table-cell">Statut</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">Téléphone</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($membres as $membre)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">
                        <span class="block">{{ $membre->nom }} {{ $membre->prenom }}</span>
                        <span class="sm:hidden text-xs text-slate-500 dark:text-slate-400 mt-1 block">
                            <span class="tabular-nums">Entrée le {{ $membre->resolveDateEntreeEglise()?->format('d/m/Y') ?? '—' }}</span>
                            · {{ $modesEntree[$membre->mode_entree] ?? '—' }} · {{ $membre->typeStatut?->libelle ?? '—' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 tabular-nums hidden md:table-cell">{{ $membre->resolveDateEntreeEglise()?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell">{{ $modesEntree[$membre->mode_entree] ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden lg:table-cell">{{ $membre->typeStatut?->libelle ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell">{{ $membre->telephone ?? '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions">
                            @can('create', App\Models\RecapSabbatEglise::class)
                            @if (auth()->user()->eglise_locale_id !== null && (int) $membre->eglise_locale_id === (int) auth()->user()->eglise_locale_id)
                            <button
                                type="button"
                                class="js-open-recette-modal inline-flex items-center gap-1.5 rounded-lg border border-indigo-300 bg-indigo-50/90 dark:bg-indigo-950/40 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 shadow-sm hover:bg-indigo-100/90 dark:hover:bg-indigo-900/50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                data-action="{{ route('finances.recaps.contribution-membre.store', $membre) }}"
                                data-membre-id="{{ $membre->id }}"
                                data-membre-nom="{{ $membre->nom }} {{ $membre->prenom }}"
                                @disabled(isset($typesRecetteContribution) && $typesRecetteContribution->isEmpty())
                                title="Enregistrer une recette de l'église">
                                Recette église
                            </button>
                            @endif
                            @endcan
                            @can('view', $membre)
                            <x-action-button variant="view" href="{{ route('membres.show', $membre) }}" />
                            @endcan
                            @can('update', $membre)
                            <x-action-button variant="edit" href="{{ route('membres.edit', $membre) }}" custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30" />
                            @endcan
                            @can('delete', $membre)
                            <x-action-button variant="delete" action="{{ route('membres.destroy', $membre) }}" method="DELETE" confirm-message="Supprimer définitivement cette fiche membre ?" />
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Aucun membre ne correspond aux critères.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($membres->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40">
        {{ $membres->links() }}
    </div>
    @endif
</div>

@if (auth()->user()->eglise_locale_id !== null && auth()->user()->can('create', App\Models\RecapSabbatEglise::class))
<div id="recette-member-modal" class="fixed inset-0 z-1200 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60" data-close-recette-modal></div>
    <div class="relative z-1201 w-[95%] max-w-2xl adventiste-card-pro-static shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 dark:border-slate-700 px-5 py-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Enregistrement d'une recette de l'église</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    <span id="recette-modal-member-label">Membre</span>
                </p>
            </div>
            <button type="button" class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" data-close-recette-modal aria-label="Fermer">✕</button>
        </div>

        <form id="recette-member-form" method="post" action="#" class="space-y-5 px-5 py-5">
            @csrf
            <input type="hidden" name="membre_modal_id" id="membre_modal_id" value="{{ old('membre_modal_id') }}">

            @if (isset($typesRecetteContribution) && $typesRecetteContribution->isEmpty())
                <div class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                    Aucun type de recette actif. Configurez-les dans les paramètres mission.
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="modal_date_sabbat" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Date du sabbat</label>
                    <input type="date" name="date_sabbat" id="modal_date_sabbat" value="{{ old('date_sabbat') }}" required
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    @error('date_sabbat')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="modal_type_recette_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Type de recette</label>
                    <select name="type_recette_id" id="modal_type_recette_id" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        <option value="">—</option>
                        @foreach (($typesRecetteContribution ?? collect()) as $t)
                            <option value="{{ $t->id }}" data-categorie="{{ $t->categorie }}" @selected((string) old('type_recette_id') === (string) $t->id)>{{ $t->libelle }}</option>
                        @endforeach
                    </select>
                    @error('type_recette_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="modal_montant" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Montant (FCFA)</label>
                <input type="text" name="montant" id="modal_montant" value="{{ old('montant') }}" required inputmode="decimal" placeholder="10 000.00"
                    class="js-montant-fcfa w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Format attendu : 10 000.00</p>
                @error('montant')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div id="modal-bloc-don-only">
                <label for="modal_designation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Motif / désignation (optionnel)</label>
                <input type="text" name="designation" id="modal_designation" value="{{ old('designation') }}"
                    class="js-modal-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                    placeholder="Ex: Don construction, offrande spéciale...">
            </div>
            <div id="modal-bloc-don-only-grid-1" class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="modal_mode_don" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mode du don</label>
                    <select name="mode_don" id="modal_mode_don" class="js-modal-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        <option value="argent" @selected(old('mode_don', 'argent') === 'argent')>Argent</option>
                        <option value="nature" @selected(old('mode_don') === 'nature')>Nature</option>
                    </select>
                </div>
                <div>
                    <label for="modal_destination_don" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Destination du don</label>
                    <select name="destination_don" id="modal_destination_don" class="js-modal-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        <option value="locale" @selected(old('destination_don', 'locale') === 'locale')>Reste local</option>
                        <option value="mission" @selected(old('destination_don') === 'mission')>Mission sans partage</option>
                    </select>
                </div>
            </div>
            <div id="modal-bloc-don-only-grid-2" class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="modal_quantite_nature" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Quantité nature (optionnel)</label>
                    <input type="number" step="0.01" min="0" name="quantite_nature" id="modal_quantite_nature" value="{{ old('quantite_nature') }}"
                        class="js-modal-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="modal_unite_nature" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Unité (optionnel)</label>
                    <input type="text" name="unite_nature" id="modal_unite_nature" value="{{ old('unite_nature') }}"
                        class="js-modal-don-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                        placeholder="Sac, kg, carton...">
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3 pt-2">
                <button type="button" class="adventiste-btn-secondary" data-close-recette-modal>Annuler</button>
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    (function () {
        var modal = document.getElementById('recette-member-modal');
        if (!modal) return;

        var form = document.getElementById('recette-member-form');
        var memberLabel = document.getElementById('recette-modal-member-label');
        var memberIdInput = document.getElementById('membre_modal_id');
        var typeSelect = document.getElementById('modal_type_recette_id');

        var openModal = function () {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };
        var closeModal = function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('.js-open-recette-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = btn.getAttribute('data-action') || '#';
                var membreNom = btn.getAttribute('data-membre-nom') || 'Membre';
                var membreId = btn.getAttribute('data-membre-id') || '';
                form.setAttribute('action', action);
                memberLabel.textContent = membreNom;
                if (memberIdInput) memberIdInput.value = membreId;
                openModal();
            });
        });

        modal.querySelectorAll('[data-close-recette-modal]').forEach(function (btn) {
            btn.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        var toggleDonFields = function () {
            if (!typeSelect) return;
            var option = typeSelect.options[typeSelect.selectedIndex];
            var cat = option ? option.getAttribute('data-categorie') : '';
            var isDon = String(cat || '') === 'don';
            ['modal-bloc-don-only', 'modal-bloc-don-only-grid-1', 'modal-bloc-don-only-grid-2'].forEach(function (id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.style.display = isDon ? '' : 'none';
            });
            document.querySelectorAll('.js-modal-don-input').forEach(function (input) {
                input.disabled = !isDon;
            });
        };

        if (typeSelect) {
            typeSelect.addEventListener('change', toggleDonFields);
        }
        toggleDonFields();

        @if ($errors->any() && old('membre_modal_id'))
            var memberButton = document.querySelector('.js-open-recette-modal[data-membre-id="{{ old('membre_modal_id') }}"]');
            if (memberButton) {
                var action = memberButton.getAttribute('data-action') || '#';
                var membreNom = memberButton.getAttribute('data-membre-nom') || 'Membre';
                form.setAttribute('action', action);
                memberLabel.textContent = membreNom;
            }
            openModal();
        @endif
    })();
</script>
@endpush
