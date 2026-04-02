@php
    $u = $utilisateur ?? null;
    $isEdit = (bool) $u;
    $field = 'w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $select = str_replace('max-w-xl', 'max-w-md', $field);
    $roleIdsEglise = array_map('intval', $roleIdsRequiringEglise ?? []);
    $oldRoleId = (int) old('role_id', $u?->role_id);
    $showEglise = in_array($oldRoleId, $roleIdsEglise, true);
@endphp

<div class="space-y-6">
    <div>
        <label for="name" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Nom complet</label>
        <input type="text" name="name" id="name" required value="{{ old('name', $u?->name) }}" class="{{ $field }}" autocomplete="name">
        @error('name')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Adresse e-mail</label>
        <input type="email" name="email" id="email" required value="{{ old('email', $u?->email) }}" class="{{ $field }}" autocomplete="email">
        @error('email')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Mot de passe @if ($isEdit)<span class="font-normal text-slate-500">(optionnel)</span>@endif</label>
        <input type="password" name="password" id="password" class="{{ $field }}" autocomplete="new-password" @if (! $isEdit) required @endif>
        @if ($isEdit)
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-500">Laisser vide pour conserver le mot de passe actuel.</p>
        @endif
        @error('password')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Confirmation du mot de passe</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="{{ $field }}" autocomplete="new-password" @if (! $isEdit) required @endif>
    </div>

    <div>
        <label for="role_id" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Rôle</label>
        <select name="role_id" id="role_id" class="{{ $select }}" required>
            @foreach ($roles as $r)
                <option value="{{ $r->id }}" @selected((string) $oldRoleId === (string) $r->id)>{{ $r->label }}</option>
            @endforeach
        </select>
        @error('role_id')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div id="eglise-row" class="{{ $showEglise ? '' : 'hidden' }}">
        <label for="eglise_locale_id" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Église locale</label>
        <select name="eglise_locale_id" id="eglise_locale_id" class="{{ $select }}">
            <option value="">— Choisir —</option>
            @foreach ($eglises as $eglise)
                <option value="{{ $eglise->id }}" @selected((string) old('eglise_locale_id', $u?->eglise_locale_id) === (string) $eglise->id)>{{ $eglise->nom }} ({{ $eglise->code_unique }})</option>
            @endforeach
        </select>
        @error('eglise_locale_id')
            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

@push('scripts')
<script>
(function ($) {
    $(function () {
        var roleIdsEglise = @json($roleIdsEglise);
        function toggleEglise() {
            var v = parseInt($('#role_id').val(), 10);
            if (roleIdsEglise.indexOf(v) !== -1) {
                $('#eglise-row').removeClass('hidden');
            } else {
                $('#eglise-row').addClass('hidden');
            }
        }
        $('#role_id').on('change', toggleEglise);
        toggleEglise();
    });
})(window.jQuery);
</script>
@endpush
