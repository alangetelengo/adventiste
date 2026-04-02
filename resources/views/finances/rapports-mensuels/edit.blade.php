@extends('layouts.app')

@section('page-title', 'Signatures — rapport mensuel')

@section('page-title-info')
    @php
        $nomsMois = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    @endphp
    {{ $nomsMois[(int) $rapport->mois] ?? $rapport->mois }} {{ $rapport->annee }} — {{ $rapport->egliseLocale->nom }}
@endsection

@section('content')
    <div class="max-w-2xl rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm p-6">
        <form method="post" action="{{ route('finances.rapports-mensuels.update', $rapport) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Signatures</h2>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="signe_tresorier" value="1" class="rounded border-slate-300" @checked(old('signe_tresorier', $rapport->signe_tresorier))>
                        Signé trésorier
                    </label>
                    <input type="date" name="date_signature_tresorier" value="{{ old('date_signature_tresorier', $rapport->date_signature_tresorier?->format('Y-m-d')) }}"
                        class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-sm">
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="signe_pasteur" value="1" class="rounded border-slate-300" @checked(old('signe_pasteur', $rapport->signe_pasteur))>
                        Signé pasteur
                    </label>
                    <input type="date" name="date_signature_pasteur" value="{{ old('date_signature_pasteur', $rapport->date_signature_pasteur?->format('Y-m-d')) }}"
                        class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-sm">
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="signe_secretaire" value="1" class="rounded border-slate-300" @checked(old('signe_secretaire', $rapport->signe_secretaire))>
                        Signé secrétaire
                    </label>
                    <input type="date" name="date_signature_secretaire" value="{{ old('date_signature_secretaire', $rapport->date_signature_secretaire?->format('Y-m-d')) }}"
                        class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-2 py-1.5 text-sm">
                </div>
            </div>

            <input type="hidden" name="verrouiller" value="0">
            <div class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50/80 dark:bg-amber-950/30 p-4">
                <label class="flex items-start gap-3 text-sm text-slate-800 dark:text-slate-200">
                    <input type="checkbox" name="verrouiller" value="1" class="mt-0.5 rounded border-slate-300" @checked(old('verrouiller') === '1')>
                    <span><strong>Verrouiller le rapport</strong> — plus de régénération ni de modification des signatures tant qu’il n’est pas supprimé (suppression impossible une fois verrouillé).</span>
                </label>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">
                    Enregistrer
                </button>
                <a href="{{ route('finances.rapports-mensuels.show', $rapport) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
