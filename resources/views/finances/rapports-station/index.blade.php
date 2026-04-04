@extends('layouts.app')

@section('page-title', 'Rapports de station — Finances mission')

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">Vue trésorerie mensuelle par station</span>
@endsection

@section('btn-create')
@can('create', App\Models\RapportStationMission::class)
<a href="{{ route('finances.rapports-station.create') }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouveau rapport
</a>
@endcan
@endsection

@section('content')
<div class="adventiste-card-pro-static overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Période</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Lignes</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Statut</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($rapports as $rapport)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">
                        {{ $rapport->mois }}/{{ $rapport->annee }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-500/30 tabular-nums">
                            {{ $rapport->lignes_count ?? 0 }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if ($rapport->dernier_remplissage_auto_le)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100/90 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200">Complété</span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-100/90 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">Brouillon</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        @can('view', $rapport)
                        <x-action-button variant="edit" href="{{ route('finances.rapports-station.show', $rapport) }}" custom-classes="text-emerald-600 dark:text-emerald-400 hover:underline text-xs font-semibold group-hover:text-emerald-700 dark:group-hover:text-emerald-300 bg-transparent border-0 p-0" text="Afficher" />
                        @endcan
                        @can('update', $rapport)
                        <x-action-button variant="edit" href="{{ route('finances.rapports-station.edit', $rapport) }}" custom-classes="text-emerald-600 dark:text-emerald-400 hover:underline text-xs font-semibold group-hover:text-emerald-700 dark:group-hover:text-emerald-300 bg-transparent border-0 p-0" />
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <p>Aucun rapport de station enregistré.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($rapports->hasPages())
<div class="mt-6">
    {{ $rapports->links() }}
</div>
@endif
@endsection
