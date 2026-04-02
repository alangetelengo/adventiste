<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="nom" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Nom du ministère/département *</label>
            <input type="text" name="nom" id="nom" value="{{ old('nom', $departement?->nom ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Ex: École du Sabbat, Ministère personnel, Département laïque" />
            @error('nom')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="code_unique" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Code unique *</label>
            <input type="text" name="code_unique" id="code_unique" value="{{ old('code_unique', $departement?->code_unique ?? '') }}" required maxlength="64" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent font-mono text-sm" placeholder="EDS, MIN_PERS, DEPT_LAIQUE" />
            @error('code_unique')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="actif" class="flex items-center gap-3">
            <input type="checkbox" name="actif" id="actif" value="1" @checked(old('actif', $departement?->actif ?? true))
            class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500"
            />
            <span class="text-sm font-medium text-slate-900 dark:text-white">Ce ministère/département est actif</span>
        </label>
    </div>
</div>
