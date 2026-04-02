@php
    $m = $membre ?? null;
    $field = 'w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $fieldGrid = trim(preg_replace('/\s*max-w-xl\s*/', ' ', $field));
    $select = str_replace('max-w-xl', 'max-w-md', $field);
    $selectGrid = trim(preg_replace('/\s*max-w-md\s*/', ' ', $select));
    $textarea = str_replace('max-w-xl', 'max-w-2xl', $field).' min-h-[5rem]';
@endphp

<div class="space-y-6">
    <div class="space-y-4">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-200/80 dark:border-slate-600/60 pb-2">Rattachement</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:items-end">
            @if (auth()->user()->eglise_locale_id !== null)
                <div>
                    <input type="hidden" name="eglise_locale_id" value="{{ auth()->user()->eglise_locale_id }}">
                    <p class="text-sm leading-snug text-slate-600 dark:text-slate-400">
                        Église : <strong class="text-slate-800 dark:text-slate-200">{{ auth()->user()->egliseLocale?->nom }}</strong>
                        <span class="font-mono text-xs text-slate-500">({{ auth()->user()->egliseLocale?->code_unique }})</span>
                    </p>
                </div>
            @else
                <div>
                    <label for="eglise_locale_id" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Église locale</label>
                    <select name="eglise_locale_id" id="eglise_locale_id" class="{{ $selectGrid }} w-full" required>
                        <option value="">— Choisir —</option>
                        @foreach ($eglises as $eglise)
                            <option value="{{ $eglise->id }}" @selected((string) old('eglise_locale_id', $m?->eglise_locale_id ?? $egliseParDéfaut ?? '') === (string) $eglise->id)>{{ $eglise->nom }} ({{ $eglise->code_unique }})</option>
                        @endforeach
                    </select>
                    @error('eglise_locale_id')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div>
                <label for="groupe_mission_id" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Groupe mission (optionnel)</label>
                <select name="groupe_mission_id" id="groupe_mission_id" class="{{ $selectGrid }} w-full">
                    <option value="">— Aucun —</option>
                    @foreach ($groupes as $g)
                        <option value="{{ $g->id }}" @selected((string) old('groupe_mission_id', $m?->groupe_mission_id) === (string) $g->id)>{{ $g->nom }}</option>
                    @endforeach
                </select>
                @error('groupe_mission_id')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-200/80 dark:border-slate-600/60 pb-2">Identité</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="nom" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Nom</label>
                <input type="text" name="nom" id="nom" required value="{{ old('nom', $m?->nom) }}" class="{{ $fieldGrid }}" autocomplete="family-name">
                @error('nom')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="prenom" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Prénom</label>
                <input type="text" name="prenom" id="prenom" required value="{{ old('prenom', $m?->prenom) }}" class="{{ $fieldGrid }}" autocomplete="given-name">
                @error('prenom')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="sexe" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Sexe</label>
                <select name="sexe" id="sexe" class="{{ $selectGrid }} w-full">
                    <option value="">—</option>
                    <option value="M" @selected(old('sexe', $m?->sexe) === 'M')>M</option>
                    <option value="F" @selected(old('sexe', $m?->sexe) === 'F')>F</option>
                </select>
                @error('sexe')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="date_naissance" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de naissance</label>
                <input type="date" name="date_naissance" id="date_naissance" value="{{ old('date_naissance', $m?->date_naissance?->format('Y-m-d')) }}" class="{{ $fieldGrid }}">
                @error('date_naissance')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div>
            <label for="lieu_naissance" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Lieu de naissance</label>
            <input type="text" name="lieu_naissance" id="lieu_naissance" value="{{ old('lieu_naissance', $m?->lieu_naissance) }}" class="{{ $field }}">
            @error('lieu_naissance')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:max-w-3xl">
            <div>
                <label for="telephone" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Téléphone</label>
                <input type="text" name="telephone" id="telephone" value="{{ old('telephone', $m?->telephone) }}" class="{{ $field }}" autocomplete="tel">
                @error('telephone')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="occupation" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Occupation</label>
                <input type="text" name="occupation" id="occupation" value="{{ old('occupation', $m?->occupation) }}" class="{{ $field }}">
                @error('occupation')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div>
            <label for="adresses" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Adresse(s)</label>
            <textarea name="adresses" id="adresses" class="{{ $textarea }}" rows="3">{{ old('adresses', $m?->adresses) }}</textarea>
            @error('adresses')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-200/80 dark:border-slate-600/60 pb-2">Famille &amp; études</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:max-w-3xl">
            <div>
                <label for="noms_pere" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Noms du père</label>
                <input type="text" name="noms_pere" id="noms_pere" value="{{ old('noms_pere', $m?->noms_pere) }}" class="{{ $field }}">
                @error('noms_pere')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="noms_mere" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Noms de la mère</label>
                <input type="text" name="noms_mere" id="noms_mere" value="{{ old('noms_mere', $m?->noms_mere) }}" class="{{ $field }}">
                @error('noms_mere')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 max-w-3xl">
            <div>
                <label for="niveau_etudes" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Niveau d’études</label>
                <input type="text" name="niveau_etudes" id="niveau_etudes" value="{{ old('niveau_etudes', $m?->niveau_etudes) }}" class="{{ $field }}">
                @error('niveau_etudes')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="situation_matrimoniale" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Situation matrimoniale</label>
                <input type="text" name="situation_matrimoniale" id="situation_matrimoniale" value="{{ old('situation_matrimoniale', $m?->situation_matrimoniale) }}" class="{{ $field }}">
                @error('situation_matrimoniale')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 max-w-3xl">
            <div>
                <label for="date_mariage" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de mariage</label>
                <input type="date" name="date_mariage" id="date_mariage" value="{{ old('date_mariage', $m?->date_mariage?->format('Y-m-d')) }}" class="{{ $field }}">
                @error('date_mariage')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="conjoint" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Conjoint(e)</label>
                <input type="text" name="conjoint" id="conjoint" value="{{ old('conjoint', $m?->conjoint) }}" class="{{ $field }}">
                @error('conjoint')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-200/80 dark:border-slate-600/60 pb-2">Vie spirituelle</h3>
        <div class="grid gap-6 sm:grid-cols-2 max-w-3xl">
            <div>
                <label for="date_bapteme" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de baptême</label>
                <input type="date" name="date_bapteme" id="date_bapteme" value="{{ old('date_bapteme', $m?->date_bapteme?->format('Y-m-d')) }}" class="{{ $field }}">
                @error('date_bapteme')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="lieu_bapteme" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Lieu de baptême</label>
                <input type="text" name="lieu_bapteme" id="lieu_bapteme" value="{{ old('lieu_bapteme', $m?->lieu_bapteme) }}" class="{{ $field }}">
                @error('lieu_bapteme')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 max-w-3xl">
            <div>
                <label for="religion_anterieure" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Religion antérieure</label>
                <input type="text" name="religion_anterieure" id="religion_anterieure" value="{{ old('religion_anterieure', $m?->religion_anterieure) }}" class="{{ $field }}">
                @error('religion_anterieure')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="baptise_par" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Baptisé(e) par</label>
                <input type="text" name="baptise_par" id="baptise_par" value="{{ old('baptise_par', $m?->baptise_par) }}" class="{{ $field }}">
                @error('baptise_par')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 max-w-3xl">
            <div>
                <label for="recu_dans_eglise_de" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Reçu(e) dans l’église de</label>
                <input type="text" name="recu_dans_eglise_de" id="recu_dans_eglise_de" value="{{ old('recu_dans_eglise_de', $m?->recu_dans_eglise_de) }}" class="{{ $field }}">
                @error('recu_dans_eglise_de')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="recu_le" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Reçu(e) le</label>
                <input type="date" name="recu_le" id="recu_le" value="{{ old('recu_le', $m?->recu_le?->format('Y-m-d')) }}" class="{{ $field }}">
                @error('recu_le')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div>
            <label for="observations" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Observations</label>
            <textarea name="observations" id="observations" class="{{ $textarea }}" rows="4">{{ old('observations', $m?->observations) }}</textarea>
            @error('observations')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
