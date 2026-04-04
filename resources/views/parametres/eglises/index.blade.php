@extends('layouts.app')

@section('page-title', 'Églises locales')

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom }}</span>
@endsection

@section('btn-create')
@can('create', App\Models\EgliseLocale::class)
<a href="{{ route('parametres.eglises.create') }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouvelle église
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
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">District</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Statut</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($eglises as $eglise)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">{{ $eglise->nom }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-slate-600 dark:text-slate-400">{{ $eglise->code_unique }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell">{{ $eglise->district?->nom ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @if ($eglise->actif)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-200 border border-emerald-200/50 dark:border-emerald-700/50">Active</span>
                        @else
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-600 dark:text-slate-300 border border-slate-200/50 dark:border-slate-500/30">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions sur l’église">
                            <x-action-button variant="view" href="{{ route('parametres.eglises.show', $eglise) }}" />
                            <a href="{{ route('parametres.eglises.departements.index', $eglise) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200/50 dark:border-indigo-800/50 bg-indigo-50/80 dark:bg-indigo-950/30 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 shadow-sm hover:bg-indigo-100/80 dark:hover:bg-indigo-900/40 hover:border-indigo-200 dark:hover:border-indigo-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-400/30" title="Ministères & Départements">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4" /></svg>
                                <span>Ministères</span>
                            </a>
                            @can('update', $eglise)
                            <x-action-button variant="edit" href="{{ route('parametres.eglises.edit', $eglise) }}" custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30" />
                            @endcan
                            @can('delete', $eglise)
                            <x-action-button variant="delete" action="{{ route('parametres.eglises.destroy', $eglise) }}" method="DELETE" confirm-message="Supprimer cette église ? Les données liées (récaps, membres, etc.) peuvent être supprimées en cascade." />
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <div class="mx-auto max-w-sm">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 mb-4">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">Aucune église enregistrée pour cette mission.</p>
                            @can('create', App\Models\EgliseLocale::class)
                            <a href="{{ route('parametres.eglises.create') }}" class="adventiste-btn-primary mt-5">Créer la première église</a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($eglises->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40">
        {{ $eglises->links() }}
    </div>
    @endif
</div>
@endsection
