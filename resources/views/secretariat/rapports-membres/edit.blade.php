@extends('layouts.app')

@section('page-title', 'Modifier rapport membres')

@section('page-title-info')
    {{ $typesPeriode[$rapport->type_periode] ?? $rapport->type_periode }} — {{ $rapport->annee }} @if($rapport->mois > 0) (mois {{ $rapport->mois }}) @endif
@endsection

@section('content')
    <div class="max-w-2xl adventiste-card-pro-static p-6">
        <form method="post" action="{{ route('secretariat.rapports-membres.update', $rapport) }}" class="space-y-6" data-offline-queue>
            @csrf
            @method('PUT')

            <div>
                <label for="notes_locales" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes du secrétaire</label>
                <textarea name="notes_locales" id="notes_locales" rows="5" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">{{ old('notes_locales', $rapport->notes_locales) }}</textarea>
                @error('notes_locales')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <input type="hidden" name="recalculer" value="0">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="recalculer" value="1" class="rounded border-slate-300">
                Recalculer les statistiques à partir des données actuelles
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
                    Enregistrer
                </button>
                <a href="{{ route('secretariat.rapports-membres.show', $rapport) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
