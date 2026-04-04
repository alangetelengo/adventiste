@php
    $m = $membre ?? null;
    $canEditGroupeMission = auth()->user()?->hasRole('secretaire_executif_mission')
        || auth()->user()?->hasRole('president_mission');
    $modesEntree = \App\Models\Membre::labelsModesEntree();
    $typesBaptemeEntree = \App\Models\Membre::labelsTypesBaptemeEntree();
    $field = 'w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $fieldGrid = trim(preg_replace('/\s*max-w-xl\s*/', ' ', $field));
    $select = str_replace('max-w-xl', 'max-w-md', $field);
    $selectGrid = trim(preg_replace('/\s*max-w-md\s*/', ' ', $select));
    $textarea = str_replace('max-w-xl', 'max-w-2xl', $field).' min-h-[5rem]';
    $textareaFull = trim(preg_replace('/\s*max-w-2xl\s*/', ' ', str_replace('max-w-xl', 'max-w-2xl', $field))).' min-h-[5rem] w-full';
@endphp

<div class="space-y-6">
    <div class="space-y-4">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-200/80 dark:border-slate-600/60 pb-2">Rattachement</h3>
        <div class="membre-form-rattachement gap-4">
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

            @if ($canEditGroupeMission)
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
                    @can('viewAny', App\Models\GroupeMission::class)
                        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-500">
                            <a href="{{ route('parametres.groupes-mission.index') }}" class="font-semibold text-[#00b464] hover:underline">Gérer les groupes mission</a>
                        </p>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    {{-- Grille 3 colonnes (CSS .membre-form-body-grid) : Identité → Famille → Vie spirituelle --}}
    <div class="membre-form-body-grid gap-4">
        <h3 class="membre-form-body-grid__full text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-200/80 dark:border-slate-600/60 pb-2">Identité</h3>
        <div>
            <label for="nom" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Nom</label>
            <input type="text" name="nom" id="nom" required value="{{ old('nom', $m?->nom) }}" class="{{ $fieldGrid }} min-w-0" autocomplete="family-name">
            @error('nom')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="prenom" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Prénom</label>
            <input type="text" name="prenom" id="prenom" required value="{{ old('prenom', $m?->prenom) }}" class="{{ $fieldGrid }} min-w-0" autocomplete="given-name">
            @error('prenom')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="sexe" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Sexe</label>
            <select name="sexe" id="sexe" class="{{ $selectGrid }} w-full min-w-0">
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
            <input type="date" name="date_naissance" id="date_naissance" value="{{ old('date_naissance', $m?->date_naissance?->format('Y-m-d')) }}" class="{{ $fieldGrid }} min-w-0">
            @error('date_naissance')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="lieu_naissance" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Lieu de naissance</label>
            <input type="text" name="lieu_naissance" id="lieu_naissance" value="{{ old('lieu_naissance', $m?->lieu_naissance) }}" class="{{ $fieldGrid }} min-w-0">
            @error('lieu_naissance')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="telephone" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Téléphone</label>
            <input type="text" name="telephone" id="telephone" value="{{ old('telephone', $m?->telephone) }}" class="{{ $fieldGrid }} min-w-0" autocomplete="tel">
            @error('telephone')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="occupation" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Occupation</label>
            <input type="text" name="occupation" id="occupation" value="{{ old('occupation', $m?->occupation) }}" class="{{ $fieldGrid }} min-w-0">
            @error('occupation')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-3">
            <label for="adresses" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Adresse(s)</label>
            <textarea name="adresses" id="adresses" class="{{ $textareaFull }}" rows="3">{{ old('adresses', $m?->adresses) }}</textarea>
            @error('adresses')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <h3 class="membre-form-body-grid__full text-sm font-bold text-slate-800 dark:text-slate-200 mt-2 border-y border-slate-200/80 py-4 dark:border-slate-600/60">Famille &amp; études</h3>
        <div>
            <label for="noms_pere" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Noms du père</label>
            <input type="text" name="noms_pere" id="noms_pere" value="{{ old('noms_pere', $m?->noms_pere) }}" class="{{ $fieldGrid }} min-w-0">
            @error('noms_pere')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="noms_mere" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Noms de la mère</label>
            <input type="text" name="noms_mere" id="noms_mere" value="{{ old('noms_mere', $m?->noms_mere) }}" class="{{ $fieldGrid }} min-w-0">
            @error('noms_mere')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="niveau_etudes" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Niveau d’études</label>
            <input type="text" name="niveau_etudes" id="niveau_etudes" value="{{ old('niveau_etudes', $m?->niveau_etudes) }}" class="{{ $fieldGrid }} min-w-0">
            @error('niveau_etudes')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="situation_matrimoniale" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Situation matrimoniale</label>
            <input type="text" name="situation_matrimoniale" id="situation_matrimoniale" value="{{ old('situation_matrimoniale', $m?->situation_matrimoniale) }}" class="{{ $fieldGrid }} min-w-0">
            @error('situation_matrimoniale')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="date_mariage" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de mariage</label>
            <input type="date" name="date_mariage" id="date_mariage" value="{{ old('date_mariage', $m?->date_mariage?->format('Y-m-d')) }}" class="{{ $fieldGrid }} min-w-0">
            @error('date_mariage')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="conjoint" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Conjoint(e)</label>
            <input type="text" name="conjoint" id="conjoint" value="{{ old('conjoint', $m?->conjoint) }}" class="{{ $fieldGrid }} min-w-0">
            @error('conjoint')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <h3 class="membre-form-body-grid__full text-sm font-bold text-slate-800 dark:text-slate-200 mt-2 border-y border-slate-200/80 py-4 dark:border-slate-600/60">Entrée dans l'église</h3>
        <div>
            <label for="mode_entree" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Mode d'entrée</label>
            <select name="mode_entree" id="mode_entree" class="{{ $selectGrid }} min-w-0 w-full" required>
                <option value="">— Choisir —</option>
                @foreach ($modesEntree as $key => $label)
                    <option value="{{ $key }}" @selected(old('mode_entree', $m?->mode_entree) === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('mode_entree')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p id="hint-mode-transfert" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400 hidden">
                Mode transfert : l'église d'origine et la date de réception sont obligatoires, puis le membre est activé automatiquement.
            </p>
        </div>
        <div>
            <label for="type_statut_membre_id" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Statut membre</label>
            <select name="type_statut_membre_id" id="type_statut_membre_id" class="{{ $selectGrid }} min-w-0 w-full">
                <option value="">— Sélection automatique (Actif) —</option>
                @foreach ($typesStatut as $typeStatut)
                    <option value="{{ $typeStatut->id }}" @selected((string) old('type_statut_membre_id', $m?->type_statut_membre_id) === (string) $typeStatut->id)>
                        {{ $typeStatut->libelle }}
                    </option>
                @endforeach
            </select>
            @error('type_statut_membre_id')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div id="block-type-bapteme">
            <label for="type_bapteme_entree" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Type de baptême d'entrée</label>
            <select name="type_bapteme_entree" id="type_bapteme_entree" class="{{ $selectGrid }} min-w-0 w-full">
                <option value="">— Choisir —</option>
                @foreach ($typesBaptemeEntree as $key => $label)
                    <option value="{{ $key }}" @selected(old('type_bapteme_entree', $m?->type_bapteme_entree) === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type_bapteme_entree')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <h3 class="membre-form-body-grid__full text-sm font-bold text-slate-800 dark:text-slate-200 mt-2 border-y border-slate-200/80 py-4 dark:border-slate-600/60">Vie spirituelle</h3>
        <div id="block-date-bapteme">
            <label for="date_bapteme" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de baptême</label>
            <input type="date" name="date_bapteme" id="date_bapteme" value="{{ old('date_bapteme', $m?->date_bapteme?->format('Y-m-d')) }}" class="{{ $fieldGrid }} min-w-0">
            @error('date_bapteme')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div id="block-date-admission-eglise">
            <label for="date_admission_eglise" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date d'admission au registre</label>
            <input type="date" name="date_admission_eglise" id="date_admission_eglise" value="{{ old('date_admission_eglise', $m?->date_admission_eglise?->format('Y-m-d')) }}" class="{{ $fieldGrid }} min-w-0">
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Inscription comme membre (secrétariat). Vide = même date que le baptême pour le tri et l’affichage.</p>
            @error('date_admission_eglise')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div id="block-lieu-bapteme">
            <label for="lieu_bapteme" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Lieu de baptême</label>
            <input type="text" name="lieu_bapteme" id="lieu_bapteme" value="{{ old('lieu_bapteme', $m?->lieu_bapteme) }}" class="{{ $fieldGrid }} min-w-0">
            @error('lieu_bapteme')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="religion_anterieure" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Religion antérieure</label>
            <input type="text" name="religion_anterieure" id="religion_anterieure" value="{{ old('religion_anterieure', $m?->religion_anterieure) }}" class="{{ $fieldGrid }} min-w-0">
            @error('religion_anterieure')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div id="block-baptise-par">
            <label for="baptise_par" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Baptisé(e) par</label>
            <input type="text" name="baptise_par" id="baptise_par" value="{{ old('baptise_par', $m?->baptise_par) }}" class="{{ $fieldGrid }} min-w-0">
            @error('baptise_par')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div id="block-eglise-origine">
            <label for="recu_dans_eglise_de" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Reçu(e) dans l’église de</label>
            <input type="text" name="recu_dans_eglise_de" id="recu_dans_eglise_de" value="{{ old('recu_dans_eglise_de', $m?->recu_dans_eglise_de) }}" class="{{ $fieldGrid }} min-w-0">
            @error('recu_dans_eglise_de')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div id="block-date-reception">
            <label for="recu_le" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Reçu(e) le</label>
            <input type="date" name="recu_le" id="recu_le" value="{{ old('recu_le', $m?->recu_le?->format('Y-m-d')) }}" class="{{ $fieldGrid }} min-w-0">
            @error('recu_le')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="membre-form-body-grid__full">
            <label for="observations" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Observations</label>
            <textarea name="observations" id="observations" class="{{ $textareaFull }}" rows="4">{{ old('observations', $m?->observations) }}</textarea>
            @error('observations')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const mode = document.getElementById('mode_entree');
        const blockType = document.getElementById('block-type-bapteme');
        const blockDateBapteme = document.getElementById('block-date-bapteme');
        const blockDateAdmissionEglise = document.getElementById('block-date-admission-eglise');
        const blockLieuBapteme = document.getElementById('block-lieu-bapteme');
        const blockBaptisePar = document.getElementById('block-baptise-par');
        const blockEgliseOrigine = document.getElementById('block-eglise-origine');
        const blockDateReception = document.getElementById('block-date-reception');
        const inputEgliseOrigine = document.getElementById('recu_dans_eglise_de');
        const inputDateReception = document.getElementById('recu_le');
        const hintTransfert = document.getElementById('hint-mode-transfert');
        if (!mode || !blockType) return;

        const refresh = () => {
            const isBapteme = mode.value === 'bapteme';
            const isTransfert = mode.value === 'transfert';

            blockType.style.display = isBapteme ? '' : 'none';
            if (blockDateBapteme) blockDateBapteme.style.display = isTransfert ? 'none' : '';
            if (blockDateAdmissionEglise) blockDateAdmissionEglise.style.display = isTransfert ? 'none' : '';
            if (blockLieuBapteme) blockLieuBapteme.style.display = isTransfert ? 'none' : '';
            if (blockBaptisePar) blockBaptisePar.style.display = isTransfert ? 'none' : '';
            if (blockEgliseOrigine) blockEgliseOrigine.style.display = isTransfert ? '' : 'none';
            if (blockDateReception) blockDateReception.style.display = isTransfert ? '' : 'none';
            if (hintTransfert) hintTransfert.classList.toggle('hidden', !isTransfert);

            if (inputEgliseOrigine) inputEgliseOrigine.required = isTransfert;
            if (inputDateReception) inputDateReception.required = isTransfert;
        };
        mode.addEventListener('change', refresh);
        refresh();
    })();
</script>
@endpush
