@extends('layouts.app')

@section('page-title', 'Modifier la permission')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ $permission->label }}</span>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static max-w-3xl p-6 sm:p-8">
        <form method="post" action="{{ route('parametres.permissions.update', $permission) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Nom (technique)</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $permission->name) }}"
                    class="w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80"
                    pattern="[a-z0-9._-]+" autocomplete="off">
                @error('name')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="label" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Libellé</label>
                <input type="text" name="label" id="label" required value="{{ old('label', $permission->label) }}"
                    class="w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80">
                @error('label')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="group" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Groupe (optionnel)</label>
                <input type="text" name="group" id="group" value="{{ old('group', $permission->group) }}"
                    class="w-full max-w-md rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80">
                @error('group')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('parametres.permissions.index') }}" class="adventiste-btn-secondary">Liste</a>
            </div>
        </form>
    </div>
@endsection
