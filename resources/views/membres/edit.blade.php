@extends('layouts.app')

@section('page-title', __('modules.membres.page_edit'))

@section('page-title-info')
    <span class="text-slate-600 dark:text-slate-400">{{ $membre->nom }} {{ $membre->prenom }}</span>
@endsection

@section('content')
    <div class="adventiste-card-pro-static w-full max-w-6xl p-6 sm:p-8">
        <form method="post" action="{{ route('membres.update', $membre) }}" class="space-y-8">
            @csrf
            @method('PUT')
            @include('membres._form', ['membre' => $membre])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">{{ __('modules.common.save') }}</button>
                <a href="{{ route('membres.show', $membre) }}" class="adventiste-btn-secondary">{{ __('modules.common.record_sheet') }}</a>
                <a href="{{ route('membres.index') }}" class="adventiste-btn-secondary">{{ __('modules.common.list') }}</a>
            </div>
        </form>
    </div>
@endsection
