@extends('layouts.app')

@section('page-title', __('secretariat.rapports_membres.title_new'))

@section('page-title-info')
    {{ auth()->user()->egliseLocale?->nom }}
@endsection

@section('content')
    <div class="max-w-2xl adventiste-card-pro-static p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
            {{ __('secretariat.rapports_membres.intro_create') }}
        </p>
        <form method="post" action="{{ route('secretariat.rapports-membres.store') }}" class="space-y-5" data-offline-queue>
            @csrf
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="type_periode" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('secretariat.rapports_membres.type_label') }}</label>
                    <select name="type_periode" id="type_periode" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        <option value="mensuel" @selected(old('type_periode', 'mensuel') === 'mensuel')>{{ __('secretariat.rapport_membre.type_mensuel') }}</option>
                        <option value="annuel" @selected(old('type_periode') === 'annuel')>{{ __('secretariat.rapport_membre.type_annuel') }}</option>
                    </select>
                </div>
                <div id="bloc_mois">
                    <label for="mois" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('secretariat.rapports_membres.month_label') }}</label>
                    <select name="mois" id="mois" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected((int) old('mois', now()->month) === $m)>{{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::createFromDate((int) now()->year, $m, 1)->translatedFormat('F')) }}</option>
                        @endfor
                    </select>
                    @error('mois')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="annee" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('secretariat.rapports_membres.year') }}</label>
                    <input type="number" name="annee" id="annee" min="2000" max="2100" required value="{{ old('annee', now()->year) }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    @error('annee')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="notes_locales" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('secretariat.rapports_membres.notes_optional') }}</label>
                <textarea name="notes_locales" id="notes_locales" rows="4" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">{{ old('notes_locales') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
                    {{ __('secretariat.rapports_membres.generate_report') }}
                </button>
                <a href="{{ route('secretariat.rapports-membres.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                    {{ __('ui.cancel') }}
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        (function () {
            const type = document.getElementById('type_periode');
            const blocMois = document.getElementById('bloc_mois');
            if (!type || !blocMois) return;
            const refresh = () => {
                blocMois.style.display = type.value === 'annuel' ? 'none' : '';
            };
            type.addEventListener('change', refresh);
            refresh();
        })();
    </script>
    @endpush
@endsection
