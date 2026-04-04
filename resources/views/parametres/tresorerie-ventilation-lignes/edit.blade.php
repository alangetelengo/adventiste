@extends('layouts.app')

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('page-title', 'Lignes — ventilation trésorerie mission')

@section('page-title-info')
    Ordre, libellés et pourcentages des lignes du rapport mensuel (structure type remontée GC / DAO / Union).
@endsection

@section('content')
    @include('parametres._nav')

    <div class="adventiste-card-pro-static overflow-hidden">
        <form method="post" action="{{ route('parametres.tresorerie-ventilation-lignes.update') }}" class="p-6 sm:p-8">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-slate-700/80 border-b border-slate-200 dark:border-slate-600">
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Ordre</th>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Type</th>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Code</th>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Désignation</th>
                            <th class="px-3 py-3 text-right text-xs font-bold uppercase text-slate-600 dark:text-slate-300">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($lignes as $ligne)
                            <tr class="align-top {{ $ligne->estTitre() ? 'bg-slate-50 dark:bg-slate-900/40' : '' }}">
                                <td class="px-3 py-2.5">
                                    <input type="number" name="lignes[{{ $ligne->id }}][ordre]" value="{{ old('lignes.'.$ligne->id.'.ordre', $ligne->ordre) }}" min="0" max="65535" required
                                        class="w-20 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-sm">
                                </td>
                                <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $ligne->kind }}</td>
                                <td class="px-3 py-2.5 text-slate-500 dark:text-slate-500 font-mono text-xs">{{ $ligne->code ?? '—' }}</td>
                                <td class="px-3 py-2.5">
                                    <input type="text" name="lignes[{{ $ligne->id }}][designation]" value="{{ old('lignes.'.$ligne->id.'.designation', $ligne->designation) }}" required
                                        class="w-full min-w-[12rem] rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-sm {{ $ligne->estTitre() ? 'font-semibold' : '' }}">
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    @if (in_array($ligne->kind, [\App\Models\MissionTresorerieVentilationLigne::KIND_POURCENTAGE_DIMES, \App\Models\MissionTresorerieVentilationLigne::KIND_POURCENTAGE_OFFRANDES], true))
                                        <input type="text" inputmode="decimal" name="lignes[{{ $ligne->id }}][pourcentage]" value="{{ old('lignes.'.$ligne->id.'.pourcentage', $ligne->pourcentage) }}"
                                            class="w-24 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-sm tabular-nums text-right">
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @error('lignes')
                <p class="mt-4 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="mt-8 flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
                    Enregistrer
                </button>
                <a href="{{ route('parametres.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 no-underline inline-flex items-center">
                    Retour paramètres
                </a>
            </div>
        </form>
    </div>
@endsection
