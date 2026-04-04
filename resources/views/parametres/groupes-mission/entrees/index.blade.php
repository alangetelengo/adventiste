@extends('layouts.app')

@section('page-title', 'Finances — ' . $groupe->nom)

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">Entrées financières mensuelles</span>
@endsection

@section('btn-create')
@can('update', $groupe)
<a href="{{ route('parametres.groupes-mission.entrees.create', $groupe) }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouvelle entrée
</a>
@endcan
@endsection

@section('content')
@include('parametres._nav')

<div class="adventiste-card-pro-static overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Période</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Dîmes</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Off. EDS</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Off. Budget</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Off. Mission</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Off. Autres</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($entrees as $entree)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">{{ $entree->mois }}/{{ $entree->annee }}</td>
                    <td class="px-6 py-4 text-right font-mono text-xs">{{ number_format($entree->dimes, 2, ',', ' ') }}</td>
                    <td class="px-6 py-4 text-right font-mono text-xs">{{ number_format($entree->offrande_ecole_sabbat, 2, ',', ' ') }}</td>
                    <td class="px-6 py-4 text-right font-mono text-xs">{{ number_format($entree->offrande_budget_eglise, 2, ',', ' ') }}</td>
                    <td class="px-6 py-4 text-right font-mono text-xs">{{ number_format($entree->offrande_fonds_mission, 2, ',', ' ') }}</td>
                    <td class="px-6 py-4 text-right font-mono text-xs">{{ number_format($entree->offrande_autres, 2, ',', ' ') }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        @can('update', $groupe)
                        <x-action-button variant="edit" href="{{ route('parametres.groupes-mission.entrees.edit', [$groupe, $entree]) }}" custom-classes="text-emerald-600 dark:text-emerald-400 hover:underline text-xs font-semibold group-hover:text-emerald-700 dark:group-hover:text-emerald-300 bg-transparent border-0 p-0" />
                        @endcan
                        @can('update', $groupe)
                        <x-action-button variant="delete" action="{{ route('parametres.groupes-mission.entrees.destroy', [$groupe, $entree]) }}" method="DELETE" confirm-message="Confirmez la suppression ?" custom-classes="text-red-600 dark:text-red-400 hover:underline text-xs font-semibold group-hover:text-red-700 dark:group-hover:text-red-300 bg-transparent border-0 p-0" />
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <p>Aucune entrée financière enregistrée.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($entrees->hasPages())
<div class="mt-6">
    {{ $entrees->links() }}
</div>
@endif

<div class="mt-6 text-right">
    <a href="{{ route('parametres.groupes-mission.edit', $groupe) }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-sm">
        Retour au groupe
    </a>
</div>
@endsection
