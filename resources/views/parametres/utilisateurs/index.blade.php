@extends('layouts.app')

@section('page-title', 'Utilisateurs')

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom }}</span>
@endsection

@section('btn-create')
@can('create', App\Models\User::class)
<a href="{{ route('parametres.utilisateurs.create') }}" class="adventiste-btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
    Nouvel utilisateur
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
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">E-mail</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Rôle</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden lg:table-cell">Portée</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @forelse ($utilisateurs as $u)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">
                        <span class="inline-flex flex-wrap items-center gap-2">
                            {{ $u->name }}
                            @if ($u->id === auth()->id())
                            <span class="text-[0.65rem] font-bold uppercase tracking-wide px-2 py-0.5 rounded-md bg-[#00b464]/15 text-[#00a055] dark:text-emerald-300 border border-[#00b464]/25">Vous</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell">{{ $u->email }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-600/80 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-500/30">{{ $u->libelleRole() }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-xs hidden lg:table-cell">
                        @if ($u->eglise_locale_id)
                        {{ $u->egliseLocale?->nom ?? '—' }}
                        @else
                        <span class="text-slate-500">Mission entière</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions sur l’utilisateur">
                            @can('update', $u)
                            <x-action-button variant="edit" href="{{ route('parametres.utilisateurs.edit', $u) }}" custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 focus:ring-2 focus:ring-[#00b464]/30" />
                            @endcan
                            @can('delete', $u)
                            <x-action-button variant="delete" action="{{ route('parametres.utilisateurs.destroy', $u) }}" method="DELETE" :confirm-message="__('modules.common.confirm_delete_user')" />
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Aucun utilisateur pour cette mission.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($utilisateurs->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40">
        {{ $utilisateurs->links() }}
    </div>
    @endif
</div>
@endsection
