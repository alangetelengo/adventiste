@extends('layouts.app')

@section('page-title', 'Modifier le rôle')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ $role->label }}</span>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static max-w-4xl p-6 sm:p-8">
        <form method="post" action="{{ route('parametres.roles.update', $role) }}" class="space-y-8">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                @if ($role->is_system)
                    <div class="rounded-lg border border-amber-200/80 dark:border-amber-800/50 bg-amber-50/40 dark:bg-amber-950/20 px-4 py-3 text-sm text-amber-950 dark:text-amber-100">
                        <p class="font-semibold mb-1">Rôle système</p>
                        <p class="text-amber-900/90 dark:text-amber-200/90">L’identifiant <code class="font-mono text-xs bg-white/60 dark:bg-slate-900/40 px-1 rounded">{{ $role->name }}</code> ne peut pas être modifié. Vous pouvez ajuster le libellé et les permissions.</p>
                    </div>
                @else
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Identifiant (technique)</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $role->name) }}"
                            class="w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80"
                            pattern="[a-z0-9._-]+" autocomplete="off">
                        @error('name')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
                <div>
                    <label for="label" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Libellé affiché</label>
                    <input type="text" name="label" id="label" required value="{{ old('label', $role->label) }}"
                        class="w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80">
                    @error('label')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">Permissions</h3>
                @include('parametres.roles._permissions_fields', ['selectedIds' => $role->permissions->pluck('id')->all()])
                @error('permissions')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('parametres.roles.index') }}" class="adventiste-btn-secondary">Liste</a>
            </div>
        </form>
    </div>
@endsection
