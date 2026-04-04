@extends('layouts.app')

@section('page-title', 'Récaps du sabbat')

@section('page-title-info')
@if ($egliseFiltre)
{{ $egliseFiltre->nom }} ({{ $egliseFiltre->code_unique }})
@else
Toutes les églises de la mission
@endif
@endsection

@section('btn-create')
@can('create', App\Models\RecapSabbatEglise::class)
<div class="flex flex-wrap items-center gap-2">
<a href="{{ route('finances.recaps.create') }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouveau récap
</a>
</div>
@endcan
@endsection

@php
$labelsStatut = ['brouillon' => 'Brouillon', 'soumis' => 'Soumis', 'verrouille' => 'Verrouillé'];
@endphp

@section('content')
<div class="adventiste-card-pro-static overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Date du sabbat</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Église</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Statut</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($recaps as $recap)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium whitespace-nowrap">
                        {{ $recap->date_sabbat->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                        {{ $recap->egliseLocale->nom }}
                    </td>
                    <td class="px-6 py-4">
                        @if ($recap->statut === 'verrouille')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-200 dark:bg-slate-600/80 text-slate-800 dark:text-slate-100 border border-slate-300/50 dark:border-slate-500/40">{{ $labelsStatut['verrouille'] }}</span>
                        @elseif ($recap->statut === 'soumis')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-200 border border-emerald-200/50 dark:border-emerald-700/50">{{ $labelsStatut['soumis'] }}</span>
                        @else
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-100 border border-amber-200/50 dark:border-amber-800/40">{{ $labelsStatut['brouillon'] }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @can('update', $recap)
                        <x-action-button variant="edit" href="{{ route('finances.recaps.edit', $recap) }}" custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30" />
                        @else
                        <span class="text-xs text-slate-400 dark:text-slate-500">Lecture seule</span>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Aucun récapitulatif pour cette période.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($recaps->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40">
        {{ $recaps->links() }}
    </div>
    @endif
</div>
@endsection
