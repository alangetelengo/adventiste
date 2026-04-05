@extends('layouts.app')

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('page-title', 'Types de statut membre')

@section('page-title-info')
Catégories de suivi pastoral et disciplinaire des membres.
@endsection

@section('btn-create')
@can('create', App\Models\TypeStatutMembre::class)
<a href="{{ route('parametres.types-statut-membre.create') }}" class="adventiste-btn-primary">Nouveau type</a>
@endcan
@endsection

@section('content')
@include('parametres._nav')

<div class="adventiste-card-pro-static overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/80 border-b border-slate-200 dark:border-slate-600">
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Ordre</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Libellé</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Actif</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Système</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($types as $type)
                <tr>
                    <td class="px-4 py-3 tabular-nums">{{ $type->ordre }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $type->code }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $type->libelle }}</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $type->description ?: '-' }}</td>
                    <td class="px-4 py-3">{{ $type->actif ? 'Oui' : 'Non' }}</td>
                    <td class="px-4 py-3">{{ $type->is_system ? 'Oui' : 'Non' }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        @can('update', $type)
                        <x-action-button variant="edit" href="{{ route('parametres.types-statut-membre.edit', $type) }}" custom-classes="text-[#00b464] dark:text-emerald-400 font-semibold text-xs hover:underline bg-transparent border-0 p-0" />
                        @endcan
                        @can('delete', $type)
                        <x-action-button variant="delete" action="{{ route('parametres.types-statut-membre.destroy', $type) }}" method="DELETE" :confirm-message="__('modules.common.confirm_delete_type_statut')" custom-classes="text-red-600 dark:text-red-400 font-semibold text-xs hover:underline bg-transparent border-0 p-0" />
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-500">Aucun type de statut.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
