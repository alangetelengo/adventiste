@extends('layouts.app')

@section('page-title', 'Rôles')

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom }}</span>
@endsection

@section('btn-create')
@can('create', App\Models\Role::class)
<a href="{{ route('parametres.roles.create') }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouveau rôle
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
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Libellé</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden sm:table-cell">Identifiant</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Type</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Utilisateurs</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($roles as $r)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">{{ $r->label }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-mono text-xs hidden sm:table-cell">{{ $r->name }}</td>
                    <td class="px-6 py-4">
                        @if ($r->is_system)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 border border-amber-200/70 dark:border-amber-800/50">Système</span>
                        @else
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-500/30">Personnalisé</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-500/30 tabular-nums">{{ $r->users_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions sur le rôle">
                            @can('update', $r)
                            <x-action-button variant="edit" href="{{ route('parametres.roles.edit', $r) }}" custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30" />
                            @endcan
                            @can('delete', $r)
                            <x-action-button variant="delete" action="{{ route('parametres.roles.destroy', $r) }}" method="DELETE" confirm-message="Supprimer ce rôle ? Les utilisateurs qui y sont rattachés devront être réassignés avant." />
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 text-sm">Aucun rôle.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
