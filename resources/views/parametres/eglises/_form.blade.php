@php
    $e = $eglise ?? null;
    $field =
        'w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $fieldMono = str_replace('max-w-xl', 'max-w-md font-mono', $field);
    $select = str_replace('max-w-xl', 'max-w-md', $field);
@endphp

<div class="space-y-6">
    <div>
        <label for="nom" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Nom de l’église</label>
        <input type="text" name="nom" id="nom" required value="{{ old('nom', $e?->nom) }}" class="{{ $field }}">
        @error('nom')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="code_unique" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Code unique</label>
        <input type="text" name="code_unique" id="code_unique" required value="{{ old('code_unique', $e?->code_unique) }}"
            class="{{ $fieldMono }}"
            placeholder="ex. MCB-BACONGO">
        @error('code_unique')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="district_id" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">District</label>
        <select name="district_id" id="district_id" class="{{ $select }}">
            <option value="">— Aucun —</option>
            @foreach ($districts as $d)
                <option value="{{ $d->id }}" @selected((string) old('district_id', $e?->district_id) === (string) $d->id)>{{ $d->nom }}</option>
            @endforeach
        </select>
        @error('district_id')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        @can('viewAny', App\Models\District::class)
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-500">
                <a href="{{ route('parametres.districts.index') }}" class="font-semibold text-[#00b464] hover:underline">Gérer les districts</a>
                @can('create', App\Models\District::class)
                    <span class="text-slate-400 dark:text-slate-600"> · </span>
                    <a href="{{ route('parametres.districts.create') }}" class="font-semibold text-[#00b464] hover:underline">Nouveau district</a>
                @endcan
            </p>
        @endcan
    </div>

    <input type="hidden" name="actif" value="0">
    <label class="flex items-center gap-3 rounded-xl border border-slate-200/90 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-900/40 px-4 py-3 cursor-pointer hover:border-emerald-300/50 dark:hover:border-emerald-700/50 transition-colors">
        <input type="checkbox" name="actif" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/40" @checked(old('actif', $e?->actif ?? true))>
        <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Église active</span>
    </label>

    <div>
        <label for="indicateurs_json" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Indicateurs financiers (JSON optionnel)</label>
        <textarea name="indicateurs_json" id="indicateurs_json" rows="10"
            class="w-full max-w-3xl rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50/80 dark:bg-slate-950/50 px-3.5 py-3 text-xs font-mono text-slate-800 dark:text-slate-200 shadow-inner focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80"
            placeholder='{"annee_objectif":2025,"objectif_dimes":0,...}'>{{ old('indicateurs_json', $e && $e->indicateurs_financiers ? json_encode($e->indicateurs_financiers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        @error('indicateurs_json')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-500 leading-relaxed">Laissez vide pour effacer les indicateurs. Objet JSON valide uniquement.</p>
    </div>
</div>
