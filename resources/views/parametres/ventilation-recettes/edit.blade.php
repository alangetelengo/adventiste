@extends('layouts.app')

@section('page-title', 'Ventilation des types de recettes')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">Paramètres mission — rapports mensuels et annuels</span>
@endsection

@php
    $peutModifier = auth()->user()->can('update', $regles);
@endphp

@section('content')
    @include('parametres._nav')

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200/80 bg-emerald-50/90 dark:border-emerald-800/50 dark:bg-emerald-950/30 px-4 py-3 text-sm text-emerald-900 dark:text-emerald-100">
            {{ session('success') }}
        </div>
    @endif

    <div class="adventiste-card-pro-static max-w-3xl p-6 sm:p-8 space-y-6">
        <div class="prose prose-slate dark:prose-invert max-w-none text-sm">
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed m-0">
                Définissez la <strong>part versée à la mission</strong> (en %) pour chaque type de recette saisi dans les récaps du sabbat.
                Le complément à 100&nbsp;% reste au <strong>budget de l’église locale</strong>. Ces règles alimentent les totaux des
                <strong>rapports mensuels</strong> et serviront de base aux <strong>rapports annuels</strong>.
            </p>
        </div>

        @unless ($peutModifier)
            <div class="rounded-lg border border-amber-200/80 bg-amber-50/80 dark:border-amber-800/50 dark:bg-amber-950/25 px-4 py-3 text-sm text-amber-950 dark:text-amber-100">
                Lecture seule — seuls le <strong>trésorier de mission</strong> et l’<strong>administrateur mission</strong> peuvent modifier ces paramètres.
            </div>
        @endunless

        <form method="post" action="{{ route('parametres.ventilation-recettes.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-600 pb-2">Pourcentages (part mission)</h2>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="part_mission_dime_pct" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dîmes (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="part_mission_dime_pct" id="part_mission_dime_pct"
                            value="{{ old('part_mission_dime_pct', $regles->part_mission_dime_pct) }}"
                            @disabled(! $peutModifier)
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm disabled:opacity-60">
                        @error('part_mission_dime_pct')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="part_mission_offrande_pct" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Offrandes cultuelles (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="part_mission_offrande_pct" id="part_mission_offrande_pct"
                            value="{{ old('part_mission_offrande_pct', $regles->part_mission_offrande_pct) }}"
                            @disabled(! $peutModifier)
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm disabled:opacity-60">
                        @error('part_mission_offrande_pct')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="part_mission_don_pct" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dons (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="part_mission_don_pct" id="part_mission_don_pct"
                            value="{{ old('part_mission_don_pct', $regles->part_mission_don_pct) }}"
                            @disabled(! $peutModifier)
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm disabled:opacity-60">
                        @error('part_mission_don_pct')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Exemple courant : dîmes 100&nbsp;%, offrandes 50&nbsp;%, dons 0&nbsp;% (tout le don reste localement).</p>
            </div>

            <div class="space-y-5">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-600 pb-2">Libellés pour les rapports (optionnel)</h2>
                <p class="text-sm text-slate-600 dark:text-slate-400">Intitulés affichés dans les exports ou tableaux de synthèse si vous personnalisez le vocabulaire de la mission.</p>
                <div class="space-y-4">
                    <div>
                        <label for="libelle_rapport_dime" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Libellé — dîme</label>
                        <input type="text" name="libelle_rapport_dime" id="libelle_rapport_dime" maxlength="120"
                            value="{{ old('libelle_rapport_dime', $regles->libelle_rapport_dime) }}"
                            @disabled(! $peutModifier)
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm disabled:opacity-60"
                            placeholder="Dîme">
                    </div>
                    <div>
                        <label for="libelle_rapport_offrande" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Libellé — offrande</label>
                        <input type="text" name="libelle_rapport_offrande" id="libelle_rapport_offrande" maxlength="120"
                            value="{{ old('libelle_rapport_offrande', $regles->libelle_rapport_offrande) }}"
                            @disabled(! $peutModifier)
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm disabled:opacity-60"
                            placeholder="Offrande cultuelle">
                    </div>
                    <div>
                        <label for="libelle_rapport_don" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Libellé — don</label>
                        <input type="text" name="libelle_rapport_don" id="libelle_rapport_don" maxlength="120"
                            value="{{ old('libelle_rapport_don', $regles->libelle_rapport_don) }}"
                            @disabled(! $peutModifier)
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm disabled:opacity-60"
                            placeholder="Don">
                    </div>
                </div>
            </div>

            <div>
                <label for="notes_internes" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes internes (optionnel)</label>
                <textarea name="notes_internes" id="notes_internes" rows="3" maxlength="5000"
                    @disabled(! $peutModifier)
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm disabled:opacity-60">{{ old('notes_internes', $regles->notes_internes) }}</textarea>
            </div>

            @if ($peutModifier)
                <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                    <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                    <a href="{{ route('parametres.index') }}" class="adventiste-btn-secondary">Retour</a>
                </div>
            @else
                <div class="pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                    <a href="{{ route('parametres.index') }}" class="adventiste-btn-secondary">Retour</a>
                </div>
            @endif
        </form>
    </div>
@endsection
