@extends('layouts.app')

@section('page-title', 'Baptêmes')

@section('page-title-info')
@if (auth()->user()->eglise_locale_id)
<span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->egliseLocale?->nom }}</span>
@else
<span class="text-slate-600 dark:text-slate-400">Registre des baptêmes des églises de la mission</span>
@endif
@endsection

@section('btn-create')
@php
    $u = auth()->user();
    $canCreateBapteme = $u
        && (
            $u->hasPermission('baptemes.create')
            || $u->hasRole('secretaire_eglise')
            || $u->hasRole('secretaire')
            || $u->hasRole('admin_mission')
        );
@endphp
@if ($canCreateBapteme)
<a href="{{ route('baptemes.create') }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouveau baptême
</a>
@endif
@endsection

@section('content')
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden mb-6">
    <form method="get" action="{{ route('baptemes.index') }}" class="px-6 py-4 flex flex-wrap items-end gap-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
        @if ($eglisesFiltre !== null && $eglisesFiltre->isNotEmpty())
        <div class="min-w-48">
            <label for="f_eglise" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Église</label>
            <select name="eglise_locale_id" id="f_eglise" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100">
                <option value="">Toutes</option>
                @foreach ($eglisesFiltre as $e)
                <option value="{{ $e->id }}" @selected((string) request('eglise_locale_id') === (string) $e->id)>{{ $e->nom }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="min-w-48">
            <label for="f_type" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Type</label>
            <select name="type_bapteme" id="f_type" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100">
                <option value="">Tous</option>
                @foreach ($typesBapteme as $key => $label)
                <option value="{{ $key }}" @selected(request('type_bapteme') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-48 flex-1 max-w-md">
            <label for="f_q" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Recherche</label>
            <input type="search" name="q" id="f_q" value="{{ request('q') }}" placeholder="Nom, officiant..." class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="adventiste-btn-primary">Filtrer</button>
            @if (request()->hasAny(['eglise_locale_id', 'type_bapteme', 'q']))
            <a href="{{ route('baptemes.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            @endif
        </div>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Candidat</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">Type</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden sm:table-cell">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden lg:table-cell">Église</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($baptemes as $bapteme)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">
                        <span class="block">{{ $bapteme->nom }} {{ $bapteme->prenom }}</span>
                        <span class="md:hidden text-xs text-slate-500 dark:text-slate-400 mt-1 block">{{ $typesBapteme[$bapteme->type_bapteme] ?? $bapteme->type_bapteme }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell">{{ $typesBapteme[$bapteme->type_bapteme] ?? $bapteme->type_bapteme }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden sm:table-cell">{{ $bapteme->date_bapteme?->translatedFormat('d M Y') ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden lg:table-cell">{{ $bapteme->egliseLocale?->nom ?? '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions">
                            @can('view', $bapteme)
                            <x-action-button variant="view" href="{{ route('baptemes.show', $bapteme) }}" />
                            @endcan
                            @can('update', $bapteme)
                            <x-action-button variant="edit" href="{{ route('baptemes.edit', $bapteme) }}" custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30" />
                            @endcan
                            @can('certificat', $bapteme)
                            <a href="{{ route('baptemes.certificat', $bapteme) }}" class="inline-flex items-center rounded-md border border-indigo-200 dark:border-indigo-700/60 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 no-underline">Certificat</a>
                            @endcan
                            @can('delete', $bapteme)
                            <x-action-button variant="delete" action="{{ route('baptemes.destroy', $bapteme) }}" method="DELETE" confirm-message="Supprimer cet enregistrement de baptême ?" />
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Aucun baptême ne correspond aux critères.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($baptemes->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40">
        {{ $baptemes->links() }}
    </div>
    @endif
</div>
@endsection

