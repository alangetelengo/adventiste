@php
    $g = $groupe ?? null;
    $field =
        'w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $fieldMono = str_replace('max-w-xl', 'max-w-md font-mono', $field);
@endphp

<div class="space-y-6">
    <div>
        <label for="nom" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Nom du groupe</label>
        <input type="text" name="nom" id="nom" required value="{{ old('nom', $g?->nom) }}" class="{{ $field }}" placeholder="ex. Jeunesse, Diaconie">
        @error('nom')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-500 leading-relaxed">Le nom doit être unique au sein de votre mission.</p>
    </div>

    <div>
        <label for="code_unique" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Code unique</label>
        <input type="text" name="code_unique" id="code_unique" required value="{{ old('code_unique', $g?->code_unique) }}"
            class="{{ $fieldMono }}"
            placeholder="ex. MT-JEUNESSE">
        @error('code_unique')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-500 leading-relaxed">Identifiant unique dans toute l’application (finances mission, fiches membres).</p>
    </div>

    <input type="hidden" name="actif" value="0">
    <div class="flex items-start gap-3 rounded-xl border border-slate-200/80 dark:border-slate-600 bg-slate-50/80 dark:bg-slate-900/40 px-4 py-3">
        <input type="checkbox" name="actif" id="actif" value="1" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#00b464] focus:ring-[#00b464]/40"
            @checked(old('actif', $g?->actif ?? true))>
        <div>
            <label for="actif" class="text-sm font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">Groupe actif</label>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-500 leading-relaxed">Les groupes inactifs peuvent rester visibles sur d’anciennes fiches ; évitez de les supprimer s’il reste des rattachements.</p>
        </div>
    </div>
</div>
