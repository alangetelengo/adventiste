@php
    $b = $bapteme ?? null;
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    @if (auth()->user()->eglise_locale_id !== null)
        <div class="sm:col-span-2">
            <input type="hidden" name="eglise_locale_id" value="{{ auth()->user()->eglise_locale_id }}">
            <p class="text-sm leading-snug text-slate-600 dark:text-slate-400">
                Église : <strong class="text-slate-800 dark:text-slate-200">{{ auth()->user()->egliseLocale?->nom }}</strong>
                <span class="font-mono text-xs text-slate-500">({{ auth()->user()->egliseLocale?->code_unique }})</span>
            </p>
        </div>
    @else
        <div class="sm:col-span-2">
            <label for="eglise_locale_id" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Église locale</label>
            <select name="eglise_locale_id" id="eglise_locale_id" class="{{ $field }}" required>
                <option value="">— Choisir —</option>
                @foreach ($eglises as $eglise)
                    <option value="{{ $eglise->id }}" @selected((string) old('eglise_locale_id', $b?->eglise_locale_id ?? $egliseParDefaut ?? '') === (string) $eglise->id)>
                        {{ $eglise->nom }} ({{ $eglise->code_unique }})
                    </option>
                @endforeach
            </select>
            @error('eglise_locale_id')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div>
        <label for="nom" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Nom</label>
        <input type="text" name="nom" id="nom" required value="{{ old('nom', $b?->nom) }}" class="{{ $field }}">
        @error('nom')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="prenom" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Prénom</label>
        <input type="text" name="prenom" id="prenom" required value="{{ old('prenom', $b?->prenom) }}" class="{{ $field }}">
        @error('prenom')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="type_bapteme" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Type de baptême</label>
        <select name="type_bapteme" id="type_bapteme" required class="{{ $field }}">
            <option value="">— Choisir —</option>
            @foreach ($typesBapteme as $key => $label)
                <option value="{{ $key }}" @selected(old('type_bapteme', $b?->type_bapteme) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type_bapteme')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="date_bapteme" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de baptême</label>
        <input type="date" name="date_bapteme" id="date_bapteme" required value="{{ old('date_bapteme', $b?->date_bapteme?->format('Y-m-d')) }}" class="{{ $field }}">
        @error('date_bapteme')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="date_admission_eglise" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date d'admission au registre</label>
        <input type="date" name="date_admission_eglise" id="date_admission_eglise" value="{{ old('date_admission_eglise', $b?->membre?->date_admission_eglise?->format('Y-m-d')) }}" class="{{ $field }}">
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Section « Admission » du certificat. Laisser vide pour reprendre la date de baptême.</p>
        @error('date_admission_eglise')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="lieu_bapteme" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Lieu de baptême</label>
        <input type="text" name="lieu_bapteme" id="lieu_bapteme" value="{{ old('lieu_bapteme', $b?->lieu_bapteme) }}" class="{{ $field }}">
        @error('lieu_bapteme')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="officiant" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Officiant</label>
        <input type="text" name="officiant" id="officiant" value="{{ old('officiant', $b?->officiant) }}" class="{{ $field }}">
        @error('officiant')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="notes" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Notes</label>
        <textarea name="notes" id="notes" rows="4" class="{{ $field }}">{{ old('notes', $b?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const toUpperIds = ['nom', 'officiant', 'lieu_bapteme'];
        const toTitleIds = ['prenom', 'notes'];

        function toUpper(value) {
            return (value || '').toLocaleUpperCase('fr-FR');
        }

        function toTitle(value) {
            return (value || '')
                .toLocaleLowerCase('fr-FR')
                .replace(/\b\p{L}/gu, (m) => m.toLocaleUpperCase('fr-FR'));
        }

        toUpperIds.forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('blur', function () {
                this.value = toUpper(this.value.trim());
            });
        });

        toTitleIds.forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('blur', function () {
                this.value = toTitle(this.value.trim());
            });
        });
    })();
</script>
@endpush

