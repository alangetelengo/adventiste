@extends('layouts.app')

@section('page-title', __('profile.password_page_title'))

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ __('profile.password_page_subtitle') }}</span>
@endsection

@section('btn-create')
    <a href="{{ route('profile.edit') }}" class="adventiste-btn-secondary inline-flex items-center gap-2 no-underline">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        {{ __('profile.back_profile') }}
    </a>
@endsection

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
@endphp
<div class="max-w-lg mx-auto">
    <form method="post" action="{{ route('profile.password.update') }}" class="adventiste-card-pro-static p-6 sm:p-8 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="current_password" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1.5">{{ __('profile.current_password') }}</label>
            <input type="password" name="current_password" id="current_password" required autocomplete="current-password"
                class="{{ $inputClass }} @error('current_password') border-red-500 focus:ring-red-500/35 focus:border-red-500 @enderror">
            @error('current_password')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1.5">{{ __('profile.new_password') }}</label>
            <input type="password" name="password" id="password" required autocomplete="new-password"
                class="{{ $inputClass }} @error('password') border-red-500 focus:ring-red-500/35 focus:border-red-500 @enderror">
            @error('password')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1.5">{{ __('profile.confirm_password') }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                class="{{ $inputClass }}">
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="adventiste-btn-primary">{{ __('ui.save') }}</button>
            <a href="{{ route('profile.edit') }}" class="adventiste-btn-secondary inline-flex items-center justify-center no-underline">{{ __('ui.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
