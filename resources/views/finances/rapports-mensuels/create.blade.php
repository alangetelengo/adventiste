@extends('layouts.app')

@section('page-title', __('finances.rapports_mensuels.page_new'))

@section('page-title-info')
    {{ auth()->user()->egliseLocale?->nom }}
@endsection

@section('content')
    <div class="max-w-xl adventiste-card-pro-static p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
            {{ __('finances.rapports_mensuels.create_intro') }}
        </p>
        <form method="post" action="{{ route('finances.rapports-mensuels.store') }}" class="space-y-5" data-offline-queue>
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="mois" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('finances.common.month') }}</label>
                    <select name="mois" id="mois" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected((int) old('mois', now()->month) === $m)>{{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::createFromDate((int) now()->year, $m, 1)->translatedFormat('F')) }}</option>
                        @endfor
                    </select>
                    @error('mois')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="annee" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('finances.common.year') }}</label>
                    <input type="number" name="annee" id="annee" min="2000" max="2100" required value="{{ old('annee', now()->year) }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    @error('annee')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
                    {{ __('finances.rapports_mensuels.generate_report') }}
                </button>
                <a href="{{ route('finances.rapports-mensuels.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                    {{ __('ui.cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection
