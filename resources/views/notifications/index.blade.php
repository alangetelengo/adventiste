@extends('layouts.app')

@section('page-title', 'Notifications')

@section('page-title-info')
    Historique de vos notifications récentes.
@endsection

@section('btn-create')
    <form method="post" action="{{ route('notifications.read-all') }}" class="m-0">
        @csrf
        <button type="submit" class="adventiste-btn-secondary">Tout marquer comme lu</button>
    </form>
@endsection

@section('content')
    <div class="adventiste-card-pro-static overflow-hidden">
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse ($notifications as $notification)
                <div class="px-6 py-4 {{ $notification->read_at ? 'bg-white dark:bg-slate-800' : 'bg-emerald-50/40 dark:bg-emerald-900/15' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $notification->title }}</p>
                            @if ($notification->message)
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $notification->message }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-500">{{ $notification->created_at?->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('notifications.open', $notification) }}" class="text-sm text-emerald-700 dark:text-emerald-300 font-medium hover:underline no-underline">Ouvrir</a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 text-sm">
                    Aucune notification.
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
