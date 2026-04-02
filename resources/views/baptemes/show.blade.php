@extends('layouts.app')

@section('page-title', 'Fiche baptême')

@section('page-title-info')
<span class="text-slate-600 dark:text-slate-400">{{ $bapteme->nom }} {{ $bapteme->prenom }}</span>
@endsection

@section('btn-create')
<div class="flex items-center gap-2">
    @can('certificat', $bapteme)
    <a href="{{ route('baptemes.certificat', $bapteme) }}" class="adventiste-btn-primary">Certificat</a>
    @endcan
    @can('update', $bapteme)
    <a href="{{ route('baptemes.edit', $bapteme) }}" class="adventiste-btn-secondary">Modifier</a>
    @endcan
</div>
@endsection

@section('content')
@php($typesBapteme = \App\Models\Bapteme::labelsTypes())
<div class="max-w-4xl space-y-6">
    <dl class="adventiste-card-pro-static p-6 sm:p-7 grid gap-5 sm:grid-cols-2 text-sm">
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nom</dt>
            <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $bapteme->nom }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Prénom</dt>
            <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $bapteme->prenom }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Type de baptême</dt>
            <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $typesBapteme[$bapteme->type_bapteme] ?? $bapteme->type_bapteme }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Date</dt>
            <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $bapteme->date_bapteme?->translatedFormat('d M Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Lieu</dt>
            <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $bapteme->lieu_bapteme ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Officiant</dt>
            <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $bapteme->officiant ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Église locale</dt>
            <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $bapteme->egliseLocale?->nom ?? '—' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Membre lié</dt>
            <dd class="mt-1 text-slate-800 dark:text-slate-200">
                @if ($bapteme->membre)
                <a href="{{ route('membres.show', $bapteme->membre) }}" class="text-[#00b464] hover:underline">{{ $bapteme->membre->nom }} {{ $bapteme->membre->prenom }}</a>
                @else
                —
                @endif
            </dd>
        </div>
    </dl>

    @if ($bapteme->notes)
    <div class="adventiste-card-pro-static p-6 sm:p-7">
        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-2">Notes</h2>
        <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $bapteme->notes }}</p>
    </div>
    @endif

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('baptemes.index') }}" class="adventiste-btn-secondary">Retour à la liste</a>
        @can('delete', $bapteme)
        <form method="post" action="{{ route('baptemes.destroy', $bapteme) }}" class="inline m-0">
            @csrf
            @method('DELETE')
            <button type="button" class="inline-flex items-center rounded-lg border border-red-200 dark:border-red-800/80 bg-red-50/80 dark:bg-red-950/35 px-4 py-2 text-sm font-semibold text-red-700 dark:text-red-300" onclick="flashAlert('Supprimer définitivement ce baptême ?', this.closest('form'), { icon: '🗑️', danger: true, confirmText: 'Supprimer' })">Supprimer</button>
        </form>
        @endcan
    </div>
</div>
@endsection

