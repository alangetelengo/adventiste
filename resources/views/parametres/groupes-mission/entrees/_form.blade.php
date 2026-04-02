<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="annee" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Année</label>
            <input type="number" name="annee" id="annee" min="2000" max="2100" value="{{ old('annee', $entree?->annee ?? $annee ?? now()->year) }}" @if($entree) disabled @endif class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
            @if ($entree)
            <input type="hidden" name="annee" value="{{ $entree->annee }}" />
            @endif
            @error('annee')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="mois" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Mois</label>
            <select name="mois" id="mois" @if($entree) disabled @endif class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                @php
                $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                @endphp
                @for ($m = 1; $m <= 12; $m++) <option value="{{ $m }}" @selected(old('mois', $entree?->mois ?? $mois ?? now()->month) == $m)>
                    {{ $months[$m] ?? "Mois $m" }}
                    </option>
                    @endfor
            </select>
            @if ($entree)
            <input type="hidden" name="mois" value="{{ $entree->mois }}" />
            @endif
            @error('mois')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <hr class="border-slate-200 dark:border-slate-600" />

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="dimes" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Dîmes (FCFA)</label>
            <input type="text" name="dimes" id="dimes" inputmode="decimal" value="{{ old('dimes', $entree ? number_format($entree->dimes, 2, ',', ' ') : '') }}" placeholder="0.00" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
            @error('dimes')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="offrande_ecole_sabbat" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Offrande École du Sabbat (FCFA)</label>
            <input type="text" name="offrande_ecole_sabbat" id="offrande_ecole_sabbat" inputmode="decimal" value="{{ old('offrande_ecole_sabbat', $entree ? number_format($entree->offrande_ecole_sabbat, 2, ',', ' ') : '') }}" placeholder="0.00" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
            @error('offrande_ecole_sabbat')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="offrande_budget_eglise" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Offrande Budget Église (FCFA)</label>
            <input type="text" name="offrande_budget_eglise" id="offrande_budget_eglise" inputmode="decimal" value="{{ old('offrande_budget_eglise', $entree ? number_format($entree->offrande_budget_eglise, 2, ',', ' ') : '') }}" placeholder="0.00" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
            @error('offrande_budget_eglise')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="offrande_fonds_mission" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Offrande Fonds Missionnaires (FCFA)</label>
            <input type="text" name="offrande_fonds_mission" id="offrande_fonds_mission" inputmode="decimal" value="{{ old('offrande_fonds_mission', $entree ? number_format($entree->offrande_fonds_mission, 2, ',', ' ') : '') }}" placeholder="0.00" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
            @error('offrande_fonds_mission')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="offrande_autres" class="block text-sm font-medium text-slate-900 dark:text-white mb-1.5">Offrandes Autres (FCFA)</label>
            <input type="text" name="offrande_autres" id="offrande_autres" inputmode="decimal" value="{{ old('offrande_autres', $entree ? number_format($entree->offrande_autres, 2, ',', ' ') : '') }}" placeholder="0.00" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
            @error('offrande_autres')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
