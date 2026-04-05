@extends('layouts.app')

@section('page-title', __('secretariat.rapports_membres.title_index'))

@section('page-title-info')
    {{ __('secretariat.rapports_membres.subtitle_index') }}
@endsection

@section('btn-create')
    @can('create', App\Models\RapportMembreEglise::class)
        <a href="{{ route('secretariat.rapports-membres.create') }}" class="adventiste-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            {{ __('secretariat.rapports_membres.new_report') }}
        </a>
    @endcan
@endsection

@section('content')
    <div class="adventiste-card-pro-static overflow-hidden mb-6">
        <form method="get" action="{{ route('secretariat.rapports-membres.index') }}" class="px-6 py-4 flex flex-wrap items-end gap-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
            @if ($eglisesFiltre !== null && $eglisesFiltre->isNotEmpty())
                <div class="min-w-48">
                    <label for="f_eglise" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('secretariat.rapports_membres.church') }}</label>
                    <select name="eglise_locale_id" id="f_eglise" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                        <option value="">{{ __('secretariat.rapports_membres.all_f') }}</option>
                        @foreach ($eglisesFiltre as $eglise)
                            <option value="{{ $eglise->id }}" @selected((string) request('eglise_locale_id') === (string) $eglise->id)>{{ $eglise->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="min-w-40">
                <label for="f_type" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('secretariat.rapports_membres.period') }}</label>
                <select name="type_periode" id="f_type" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                    <option value="">{{ __('secretariat.rapports_membres.all_f') }}</option>
                    @foreach ($typesPeriode as $k => $v)
                        <option value="{{ $k }}" @selected(request('type_periode') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-40">
                <label for="f_etat" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('secretariat.rapports_membres.state') }}</label>
                <select name="etat" id="f_etat" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                    <option value="">{{ __('secretariat.rapports_membres.all_m') }}</option>
                    @foreach ($etats as $k => $v)
                        <option value="{{ $k }}" @selected(request('etat') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-32">
                <label for="f_annee" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('secretariat.rapports_membres.year') }}</label>
                <input type="number" name="annee" id="f_annee" min="2000" max="2100" value="{{ request('annee') }}" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="adventiste-btn-primary">{{ __('ui.filter') }}</button>
                @if (request()->hasAny(['eglise_locale_id', 'type_periode', 'etat', 'annee']))
                    <a href="{{ route('secretariat.rapports-membres.index') }}" class="adventiste-btn-secondary">{{ __('secretariat.rapports_membres.reset') }}</a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">{{ __('secretariat.rapports_membres.col_period') }}</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">{{ __('secretariat.rapports_membres.col_church') }}</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">{{ __('secretariat.rapports_membres.col_members') }}</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">{{ __('secretariat.rapports_membres.col_state') }}</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                    @forelse ($rapports as $rapport)
                        <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium whitespace-nowrap">
                                {{ $typesPeriode[$rapport->type_periode] ?? $rapport->type_periode }}
                                —
                                @if ((int) $rapport->mois > 0)
                                    {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::createFromDate((int) $rapport->annee, (int) $rapport->mois, 1)->translatedFormat('F')) }}
                                @else
                                    {{ __('secretariat.rapports_membres.full_year') }}
                                @endif
                                {{ $rapport->annee }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $rapport->egliseLocale->nom }}</td>
                            <td class="px-6 py-4 text-right tabular-nums">{{ number_format((int) $rapport->total_membres, 0, ',', ' ') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-200 dark:bg-slate-600/80 text-slate-800 dark:text-slate-100">
                                    {{ $etats[$rapport->etat] ?? $rapport->etat }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('secretariat.rapports-membres.show', $rapport) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800/90 px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 no-underline">
                                    {{ __('secretariat.rapports_membres.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 text-sm">
                                {{ __('secretariat.rapports_membres.empty_index') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rapports->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40">
                {{ $rapports->links() }}
            </div>
        @endif
    </div>
@endsection
