@extends('layouts.app')

@section('page-title', 'Permissions')

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom }}</span>
@endsection

@section('btn-create')
@can('create', App\Models\Permission::class)
<a href="{{ route('parametres.permissions.create') }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouvelle permission
</a>
@endcan
@endsection

@section('content')
@include('parametres._nav')

<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gradient-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Libellé</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">Nom</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden sm:table-cell">Groupe</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Rôles</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($permissions as $p)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">{{ $p->label }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-mono text-xs hidden md:table-cell">{{ $p->name }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden sm:table-cell">{{ $p->group ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-500/30 tabular-nums">{{ $p->roles_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions sur la permission">
                            @can('update', $p)
                            <x-action-button variant="edit" href="{{ route('parametres.permissions.edit', $p) }}" custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30" />
                            @endcan
                            @can('delete', $p)
                            <x-action-button variant="delete" action="{{ route('parametres.permissions.destroy', $p) }}" method="DELETE" confirm-message="Supprimer cette permission ? Elle ne doit être liée à aucun rôle." />
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 text-sm">Aucune permission.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
