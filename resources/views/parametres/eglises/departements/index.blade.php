@extends('layouts.app')

@section('page-title', 'Départements & ministères — ' . $eglise->nom)

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">Gestion des ministères et départements de l'église</span>
@endsection

@section('btn-create')
@can('create', App\Models\DepartementMinistere::class)
<a href="{{ route('parametres.eglises.departements.create', $eglise) }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouveau ministère
</a>
@endcan
@endsection

@section('content')
@include('parametres._nav')

<div class="rounded-xl border border-amber-200/80 dark:border-amber-700 bg-amber-50/40 dark:bg-amber-950/30 p-4 mb-6">
    <p class="text-sm text-slate-700 dark:text-slate-200">
        Pour chaque église, configurez ici les ministères et départements (ex. « École du Sabbat », « Jeunesse », « Mission », etc.).
        La collecte et la ventilation des recettes peuvent ensuite s’appuyer sur le département choisi.
    </p>
</div>

<div class="adventiste-card-pro-static overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Nom</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Statut</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($departements as $departement)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">{{ $departement->nom }}</td>
                    <td class="px-6 py-4">
                        @if ($departement->actif)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100/90 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200">Actif</span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-600 dark:text-slate-300">Inactif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions sur le département">
                            @can('update', $departement)
                            <x-action-button variant="edit" href="{{ route('parametres.eglises.departements.edit', [$eglise, $departement]) }}" />
                            @endcan
                            @can('delete', $departement)
                            <x-action-button variant="delete" action="{{ route('parametres.eglises.departements.destroy', [$eglise, $departement]) }}" method="DELETE" :confirm-message="__('modules.common.confirm_delete_departement')" />
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                        <p>Aucun ministère/département enregistré.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($departements->hasPages())
<div class="mt-6">
    {{ $departements->links() }}
</div>
@endif

<div class="mt-6 text-right">
    <a href="{{ route('parametres.eglises.show', $eglise) }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-sm">
        Retour à l'église
    </a>
</div>
@endsection
