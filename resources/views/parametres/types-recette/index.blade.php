@extends('layouts.app')

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('page-title', 'Types de recette')

@section('page-title-info')
Libellés et catégories (dîme / offrande / don) utilisés sur les récaps de toutes les églises de la mission.
@endsection

@section('btn-create')
@can('create', App\Models\TypeRecetteMission::class)
<a href="{{ route('parametres.types-recette.create') }}" class="adventiste-btn-primary">Nouveau type</a>
@endcan
@endsection

@section('content')
@include('parametres._nav')

<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/80 border-b border-slate-200 dark:border-slate-600">
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Ordre</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Libellé</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Catégorie</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Actif</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Règles mission</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($types as $type)
                <tr>
                    <td class="px-4 py-3 tabular-nums">{{ $type->ordre }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $type->code }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $type->libelle }}</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $type->categorie }}</td>
                    <td class="px-4 py-3">{{ $type->actif ? 'Oui' : 'Non' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                        @if ($type->exclure_rapport_mission)
                            Exclu rapport mission
                        @elseif ($type->mission_sans_partage)
                            100% mission
                        @else
                            Ventilation normale
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        @can('update', $type)
                        <x-action-button variant="edit" href="{{ route('parametres.types-recette.edit', $type) }}" custom-classes="text-[#00b464] dark:text-emerald-400 font-semibold text-xs hover:underline bg-transparent border-0 p-0" />
                        @endcan
                        @can('delete', $type)
                        <x-action-button variant="delete" action="{{ route('parametres.types-recette.destroy', $type) }}" method="DELETE" confirm-message="Supprimer ce type ?" custom-classes="text-red-600 dark:text-red-400 font-semibold text-xs hover:underline bg-transparent border-0 p-0" />
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-500">Aucun type.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
