@php
    $d = $district ?? null;
    $field = 'w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
@endphp

<div>
    <label for="nom" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Nom du district</label>
    <input type="text" name="nom" id="nom" required value="{{ old('nom', $d?->nom) }}" class="{{ $field }}" placeholder="ex. District de Brazzaville">
    @error('nom')
        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    <p class="mt-2 text-xs text-slate-500 dark:text-slate-500 leading-relaxed">Le nom doit être unique au sein de votre mission.</p>
</div>
