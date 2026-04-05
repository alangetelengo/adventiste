@extends('layouts.app')

@section('page-title', __('modules.baptemes.page_new'))

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ auth()->user()->mission?->nom ?? auth()->user()->egliseLocale?->nom }}</span>
@endsection

@section('content')
<div class="adventiste-card-pro-static w-full max-w-5xl p-6 sm:p-8">
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
        {{ __('modules.baptemes.create_intro') }}
    </p>
    <form method="post" action="{{ route('baptemes.store') }}" class="space-y-8" data-offline-queue>
        @csrf
        @include('baptemes._form', ['bapteme' => null])
        <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
            <button type="submit" class="adventiste-btn-primary">{{ __('modules.common.save') }}</button>
            <a href="{{ route('baptemes.index') }}" class="adventiste-btn-secondary">{{ __('modules.common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

