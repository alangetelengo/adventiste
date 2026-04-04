@extends('layouts.app')

@section('page-title', 'Groupes mission')

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom }}</span>
@endsection

@section('btn-create')
@can('create', App\Models\GroupeMission::class)
<a href="{{ route('parametres.groupes-mission.create') }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouveau groupe
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
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Nom</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Code</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Statut</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Membres</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Finances</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($groupes as $groupe)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">{{ $groupe->nom }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-slate-600 dark:text-slate-400">{{ $groupe->code_unique }}</td>
                    <td class="px-6 py-4">
                        @if ($groupe->actif)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100/90 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200">Actif</span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-600 dark:text-slate-300">Inactif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-500/30 tabular-nums">
                            {{ $groupe->membres_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-500/30 tabular-nums">
                            {{ $groupe->entrees_financieres_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions sur le groupe">
                            <x-action-button variant="view" href="{{ route('parametres.groupes-mission.show', $groupe) }}" />
                            @can('update', $groupe)
                            <x-action-button variant="edit" href="{{ route('parametres.groupes-mission.edit', $groupe) }}" custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30" />
                            @endcan
                            @can('delete', $groupe)
                            <x-action-button variant="delete" action="{{ route('parametres.groupes-mission.destroy', $groupe) }}" method="DELETE" confirm-message="Supprimer ce groupe ? Impossible s'il reste des membres ou des lignes de finances." />
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="mx-auto max-w-sm">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 mb-4">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">Aucun groupe mission pour cette mission.</p>
                            @can('create', App\Models\GroupeMission::class)
                            <a href="{{ route('parametres.groupes-mission.create') }}" class="adventiste-btn-primary mt-5">Créer un groupe</a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($groupes->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40">
        {{ $groupes->links() }}
    </div>
    @endif
</div>
@endsection
