@extends('layouts.app')

@section('page-title', 'Ventilation trésorerie mission')

@section('page-title-info')
    Rapport mensuel de ventilation (dîmes / offrandes) selon les lignes paramétrées pour votre mission.
@endsection

@php
    $nomsMois = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
@endphp

@section('content')
    <div class="mb-8 rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm p-6 sm:p-7">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Ouvrir une période</h2>
        <form class="flex flex-wrap items-end gap-4" onsubmit="return false;">
            <div>
                <label for="nav-annee" class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Année</label>
                <input type="number" id="nav-annee" name="annee" value="{{ request('annee', (int) date('Y')) }}" min="2000" max="2100" required
                    class="w-28 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="nav-mois" class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Mois</label>
                <select id="nav-mois" name="mois" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm min-w-40">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((int) request('mois', (int) date('n')) === $m)>{{ $nomsMois[$m] }}</option>
                    @endfor
                </select>
            </div>
            <a href="#" id="ventilation-open-period" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800 no-underline">
                Saisir le rapport
            </a>
        </form>
        @push('scripts')
            <script>
                document.getElementById('ventilation-open-period')?.addEventListener('click', function (e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    const y = form.querySelector('[name="annee"]').value;
                    const m = form.querySelector('[name="mois"]').value;
                    const base = @json(route('finances.ventilation-tresorerie-mission.index'));
                    window.location.href = base + '/' + encodeURIComponent(y) + '/' + encodeURIComponent(m);
                });
            </script>
        @endpush
    </div>

    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Période</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Dîmes (mois)</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Offrandes (mois)</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                    @forelse ($rapports as $rapport)
                        <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium whitespace-nowrap">
                                {{ $nomsMois[(int) $rapport->mois] ?? $rapport->mois }} {{ $rapport->annee }}
                            </td>
                            @php
                                $etat = (string) ($rapport->etat_transmission ?? \App\Models\MissionTresorerieRapportMensuel::ETAT_BROUILLON);
                                $etatClass = match ($etat) {
                                    \App\Models\MissionTresorerieRapportMensuel::ETAT_SOUMIS => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                                    \App\Models\MissionTresorerieRapportMensuel::ETAT_VALIDE_MISSION => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                                    \App\Models\MissionTresorerieRapportMensuel::ETAT_REFUSE_MISSION => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                                };
                            @endphp
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $etatClass }}">
                                    {{ \App\Models\MissionTresorerieRapportMensuel::labelsEtatsTransmission()[$etat] ?? 'Brouillon' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right tabular-nums text-slate-700 dark:text-slate-200">
                                {{ number_format((float) $rapport->totalDimesMois(), 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 text-right tabular-nums text-slate-700 dark:text-slate-200">
                                {{ number_format((float) $rapport->offrandes_mois, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('finances.ventilation-tresorerie-mission.edit', ['annee' => $rapport->annee, 'mois' => $rapport->mois]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800/90 px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-all no-underline">
                                    Ouvrir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                Aucun rapport enregistré. Choisissez une période ci-dessus pour commencer la saisie.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rapports->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                {{ $rapports->links() }}
            </div>
        @endif
    </div>
@endsection
