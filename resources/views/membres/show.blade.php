@extends('layouts.app')

@section('page-title', __('modules.membres.page_show'))

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ $membre->nom }} {{ $membre->prenom }}</span>
@endsection

@section('btn-create')
    @can('update', $membre)
        <a href="{{ route('membres.edit', $membre) }}" class="adventiste-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            {{ __('modules.common.edit') }}
        </a>
    @endcan
@endsection

@section('content')
    @php
        $modesEntree = \App\Models\Membre::labelsModesEntree();
        $typesBaptemeEntree = \App\Models\Membre::labelsTypesBaptemeEntree();
        $dash = __('modules.common.dash');
    @endphp
    <div class="max-w-4xl space-y-6">
        <dl class="adventiste-card-pro-static p-6 sm:p-7 grid gap-5 sm:grid-cols-2 text-sm">
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.show_dt_local_church') }}</dt>
                <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $membre->egliseLocale?->nom ?? $dash }} @if ($membre->egliseLocale)<span class="font-mono text-xs text-slate-500">({{ $membre->egliseLocale->code_unique }})</span>@endif</dd>
            </div>
            @if ($membre->groupeMission)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.show_dt_mission_group') }}</dt>
                    <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->groupeMission->nom }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_sexe') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->sexe ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_date_naissance') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->date_naissance?->translatedFormat('d M Y') ?? $dash }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_lieu_naissance') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->lieu_naissance ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_telephone') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->telephone ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_occupation') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->occupation ?? $dash }}</dd>
            </div>
            @if ($membre->adresses)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_adresses') }}</dt>
                    <dd class="mt-1 text-slate-800 dark:text-slate-200 whitespace-pre-wrap">{{ $membre->adresses }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_mode_entree') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $modesEntree[$membre->mode_entree] ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_statut_membre') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->typeStatut?->libelle ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_type_bapteme_entree') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $typesBaptemeEntree[$membre->type_bapteme_entree] ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_date_bapteme') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->date_bapteme?->translatedFormat('d M Y') ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_date_admission') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->date_admission_eglise?->translatedFormat('d M Y') ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.form_lieu_bapteme') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->lieu_bapteme ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.show_dt_origin_church') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->recu_dans_eglise_de ?? $dash }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('modules.membres.show_dt_reception_date') }}</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $membre->recu_le?->translatedFormat('d M Y') ?? $dash }}</dd>
            </div>
        </dl>

        @if ($membre->observations)
            <div class="adventiste-card-pro-static p-6 sm:p-7">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-2">{{ __('modules.membres.form_observations') }}</h2>
                <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $membre->observations }}</p>
            </div>
        @endif

        @can('changeStatut', $membre)
            <div class="adventiste-card-pro-static p-6 sm:p-7 space-y-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ __('modules.membres.change_status_title') }}</h2>
                <form method="post" action="{{ route('membres.change-statut', $membre) }}" class="grid gap-4 md:grid-cols-2" data-offline-queue>
                    @csrf
                    <div>
                        <label for="type_statut_membre_id" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">{{ __('modules.membres.new_status') }}</label>
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
                        <label for="motif" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">{{ __('modules.membres.motif_optional') }}</label>
                        <textarea name="motif" id="motif" rows="3" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">{{ old('motif') }}</textarea>
                        @error('motif')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="adventiste-btn-primary">{{ __('modules.membres.update_status') }}</button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="adventiste-card-pro-static p-6 sm:p-7">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">{{ __('modules.membres.history_title') }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="py-2 text-left text-xs uppercase tracking-wide text-slate-500">{{ __('modules.membres.hist_col_date') }}</th>
                            <th class="py-2 text-left text-xs uppercase tracking-wide text-slate-500">{{ __('modules.membres.hist_col_status') }}</th>
                            <th class="py-2 text-left text-xs uppercase tracking-wide text-slate-500">{{ __('modules.membres.hist_col_by') }}</th>
                            <th class="py-2 text-left text-xs uppercase tracking-wide text-slate-500">{{ __('modules.membres.hist_col_motif') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($membre->historiqueStatuts as $ligne)
                            <tr>
                                <td class="py-2 text-slate-700 dark:text-slate-200">{{ $ligne->changed_at?->translatedFormat('d M Y H:i') ?? $dash }}</td>
                                <td class="py-2 text-slate-700 dark:text-slate-200">{{ $ligne->typeStatut?->libelle ?? $dash }}</td>
                                <td class="py-2 text-slate-700 dark:text-slate-200">{{ $ligne->changePar?->name ?? __('modules.membres.hist_system') }}</td>
                                <td class="py-2 text-slate-600 dark:text-slate-300">{{ $ligne->motif ?? $dash }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500">{{ __('modules.membres.hist_empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('membres.index') }}" class="adventiste-btn-secondary">{{ __('modules.membres.back_list') }}</a>
            @can('delete', $membre)
                <form method="post" action="{{ route('membres.destroy', $membre) }}" class="inline m-0">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="inline-flex items-center rounded-lg border border-red-200 dark:border-red-800/80 bg-red-50/80 dark:bg-red-950/35 px-4 py-2 text-sm font-semibold text-red-700 dark:text-red-300" onclick="flashAlert(@json(__('modules.common.confirm_delete_member_show')), this.closest('form'), { icon: '🗑️', danger: true, confirmText: @json(__('modules.common.delete')) })">{{ __('modules.common.delete') }}</button>
                </form>
            @endcan
        </div>
    </div>
@endsection
