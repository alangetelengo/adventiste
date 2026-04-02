@extends('layouts.app')

@section('page-title', 'Nouveau rôle')

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom }}</span>
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static max-w-4xl p-6 sm:p-8">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
            Définissez un identifiant technique unique (lettres minuscules, chiffres, points) et associez les permissions applicables.
        </p>
        <form method="post" action="{{ route('parametres.roles.store') }}" class="space-y-8">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Identifiant (technique)</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                        class="w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80"
                        pattern="[a-z0-9._-]+" autocomplete="off" placeholder="ex. coordinateur_district">
                    @error('name')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="label" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2">Libellé affiché</label>
                    <input type="text" name="label" id="label" required value="{{ old('label') }}"
                        class="w-full max-w-xl rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80">
                    @error('label')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">Permissions</h3>
                @include('parametres.roles._permissions_fields', ['selectedIds' => []])
                @error('permissions')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Créer le rôle</button>
                <a href="{{ route('parametres.roles.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
