@extends('layouts.app')

@section('page-title', 'Tableau de bord')

@section('page-title-info')
@auth
@if (auth()->user()->eglise_locale_id)
<span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1 text-slate-600 dark:text-slate-400">
    <span class="font-medium text-slate-800 dark:text-slate-200">{{ auth()->user()->egliseLocale?->nom ?? '—' }}</span>
    <span class="text-slate-300 dark:text-slate-600">·</span>
    <span class="font-mono text-xs px-2 py-0.5 rounded-md bg-white/80 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-600/60">{{ auth()->user()->egliseLocale?->code_unique ?? '—' }}</span>
</span>
@elseif (auth()->user()->mission_id)
<span class="text-slate-600 dark:text-slate-400">
    Mission <strong class="text-slate-800 dark:text-slate-200">{{ auth()->user()->mission?->nom ?? '—' }}</strong>
    <span class="text-slate-400 dark:text-slate-500 text-sm ml-1">— vue consolidée</span>
</span>
@endif
@endauth
@endsection

@push('styles')
<style>
    .dashboard-panel {
        position: relative;
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.25);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 100%);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .dashboard-panel::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        pointer-events: none;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .dashboard-kpi-card {
        border: 1px solid rgba(148, 163, 184, 0.24);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .dashboard-module-card {
        border: 1px solid rgba(148, 163, 184, 0.24);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.09);
    }

    .dark .dashboard-panel {
        border-color: rgba(100, 116, 139, 0.45);
        background: linear-gradient(180deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.98) 100%);
        box-shadow: 0 12px 24px rgba(2, 6, 23, 0.45);
    }

    .dark .dashboard-kpi-card,
    .dark .dashboard-module-card {
        border-color: rgba(100, 116, 139, 0.45);
        box-shadow: 0 10px 22px rgba(2, 6, 23, 0.45);
    }
</style>
@endpush

@section('content')
@php
$moisCourant = $dashboard['periode'] ?? now()->translatedFormat('F Y');
$kpis = $dashboard['kpis'] ?? [];
$dashboardRole = $dashboard['role'] ?? 'default';
$recapsRecents = $dashboard['recapsRecents'] ?? collect();
$rapportsSoumis = $dashboard['rapportsSoumis'] ?? collect();
$rapportsMembresRecents = $dashboard['rapportsMembresRecents'] ?? collect();
$baptemesRecents = $dashboard['baptemesRecents'] ?? collect();
$rapportsFinancesMission = $dashboard['rapportsFinancesMission'] ?? collect();
$rapportsMembresMission = $dashboard['rapportsMembresMission'] ?? collect();
$priorites = collect($dashboard['priorites'] ?? []);
$comparatifMensuel = collect($dashboard['comparatifMensuel'] ?? []);
$panelHeaderTone = match ($dashboardRole) {
    'president_mission' => 'from-violet-50 to-indigo-50/70 dark:from-violet-900/20 dark:to-indigo-900/20',
    'secretaire_eglise' => 'from-blue-50 to-cyan-50/70 dark:from-blue-900/20 dark:to-cyan-900/20',
    default => 'from-slate-50 to-slate-100/70 dark:from-slate-700/50 dark:to-slate-800/50',
};
$tableHeadTone = match ($dashboardRole) {
    'president_mission' => 'bg-violet-50/80 text-violet-700 dark:bg-violet-900/25 dark:text-violet-200',
    'secretaire_eglise' => 'bg-blue-50/80 text-blue-700 dark:bg-blue-900/25 dark:text-blue-200',
    default => 'bg-slate-50 text-slate-600 dark:bg-slate-900/50 dark:text-slate-300',
};
$accentBlob = match ($dashboardRole) {
    'president_mission' => 'bg-violet-500/10 group-hover:bg-violet-500/20',
    'secretaire_eglise' => 'bg-blue-500/10 group-hover:bg-blue-500/20',
    default => 'bg-[#00b464]/10 group-hover:bg-[#00b464]/20',
};
$accentButton = match ($dashboardRole) {
    'president_mission' => 'bg-violet-600 hover:bg-violet-700',
    'secretaire_eglise' => 'bg-blue-600 hover:bg-blue-700',
    default => 'bg-[#00b464] hover:bg-[#009a55]',
};
$accentHoverTitle = match ($dashboardRole) {
    'president_mission' => 'group-hover:text-violet-600 dark:group-hover:text-violet-300',
    'secretaire_eglise' => 'group-hover:text-blue-600 dark:group-hover:text-blue-300',
    default => 'group-hover:text-[#00b464] dark:group-hover:text-emerald-300',
};
$accentLink = match ($dashboardRole) {
    'president_mission' => 'text-violet-600 dark:text-violet-300',
    'secretaire_eglise' => 'text-blue-600 dark:text-blue-300',
    default => 'text-[#00b464] dark:text-emerald-400',
};
$accentBar = match ($dashboardRole) {
    'president_mission' => 'bg-violet-500',
    'secretaire_eglise' => 'bg-blue-500',
    default => 'bg-[#00b464]',
};
@endphp

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @forelse($kpis as $kpi)
        <div class="dashboard-kpi-card group relative overflow-hidden rounded-2xl bg-white p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:bg-slate-800">
            <div class="absolute -top-8 -right-7 h-20 w-20 rounded-full transition-colors {{ $accentBlob }}"></div>
            <p class="relative text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $kpi['label'] ?? 'Indicateur' }}</p>
            <p class="relative mt-2 text-2xl sm:text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white">{{ number_format((float) ($kpi['value'] ?? 0), 0, ',', ' ') }}</p>
            <p class="relative mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $kpi['suffix'] ?? '' }}</p>
        </div>
        @empty
        <div class="adventiste-card-pro p-5 sm:col-span-2 xl:col-span-4">
            <p class="text-sm text-slate-500">Aucun indicateur disponible pour ce rôle.</p>
        </div>
        @endforelse
    </div>

    @if(!empty($roleStats['actions']))
    <div class="dashboard-panel overflow-hidden rounded-2xl bg-white dark:bg-slate-800">
        <div class="border-b border-slate-200 bg-linear-to-r {{ $panelHeaderTone }} px-5 py-4 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Actions rapides</h3>
        </div>
        <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 sm:p-5">
                @foreach($roleStats['actions'] as $action)
                @if(\Illuminate\Support\Facades\Route::has($action['route']))
                <a href="{{ route($action['route']) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:shadow no-underline {{ $accentButton }}">
                    <x-dynamic-icon :name="$action['icon']" class="w-4 h-4" />
                    {{ $action['label'] }}
                </a>
                @endif
                @endforeach
        </div>
    </div>
    @endif

    @if($priorites->isNotEmpty())
    <div class="dashboard-panel overflow-hidden rounded-2xl bg-white dark:bg-slate-800">
        <div class="border-b border-slate-200 bg-linear-to-r {{ $panelHeaderTone }} px-5 py-4 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Priorités du jour</h3>
        </div>
        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3 sm:p-5">
            @foreach($priorites as $item)
                @php
                    $tone = $item['tone'] ?? 'slate';
                    $toneClass = match ($tone) {
                        'rose' => 'border-rose-200 bg-rose-50/70 text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/20 dark:text-rose-300',
                        'amber' => 'border-amber-200 bg-amber-50/70 text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/20 dark:text-amber-300',
                        'indigo' => 'border-indigo-200 bg-indigo-50/70 text-indigo-700 dark:border-indigo-800/60 dark:bg-indigo-950/20 dark:text-indigo-300',
                        default => 'border-slate-200 bg-slate-50/70 text-slate-700 dark:border-slate-700 dark:bg-slate-900/30 dark:text-slate-300',
                    };
                @endphp
                <div class="rounded-xl border p-4 {{ $toneClass }}">
                    <div class="mb-2 flex items-start justify-between gap-3">
                        <div class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/70 dark:bg-slate-900/40">
                            <x-dynamic-icon :name="$item['icon'] ?? 'chart-bar'" class="h-4 w-4" />
                        </div>
                        <span class="inline-flex items-center rounded-full bg-white/70 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide dark:bg-slate-900/40">
                            {{ ((int) ($item['value'] ?? 0)) > 0 ? 'À traiter' : 'OK' }}
                        </span>
                    </div>
                    <p class="text-xs font-semibold uppercase tracking-wide">{{ $item['label'] ?? 'Priorité' }}</p>
                    <p class="mt-2 text-2xl sm:text-3xl font-extrabold tabular-nums">{{ number_format((int) ($item['value'] ?? 0), 0, ',', ' ') }}</p>
                    <p class="mt-2 text-xs opacity-90">{{ $item['help'] ?? '' }}</p>
                    @if(!empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']))
                        <a href="{{ route($item['route']) }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold no-underline">
                            Traiter
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($comparatifMensuel->isNotEmpty())
    @php
        $maxComparatif = (float) $comparatifMensuel->map(fn($x) => max((float) ($x['courant'] ?? 0), (float) ($x['precedent'] ?? 0)))->max();
        $maxComparatif = $maxComparatif > 0 ? $maxComparatif : 1;
    @endphp
    <div class="dashboard-panel overflow-hidden rounded-2xl bg-white dark:bg-slate-800">
        <div class="border-b border-slate-200 bg-linear-to-r {{ $panelHeaderTone }} px-5 py-4 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Performance mensuelle (courant vs précédent)</h3>
        </div>
        <div class="grid gap-4 p-4 lg:grid-cols-3 sm:p-5">
            @foreach($comparatifMensuel as $ligne)
                @php
                    $courant = (float) ($ligne['courant'] ?? 0);
                    $precedent = (float) ($ligne['precedent'] ?? 0);
                    $wCourant = min(100, round(($courant / $maxComparatif) * 100, 1));
                    $wPrecedent = min(100, round(($precedent / $maxComparatif) * 100, 1));
                    $delta = $courant - $precedent;
                @endphp
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-900/30">
                    <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $ligne['label'] ?? 'Indicateur' }}</p>
                        <p class="text-xs {{ $delta >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                            {{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 0, ',', ' ') }} {{ $ligne['suffix'] ?? '' }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                <span>Mois courant</span>
                                <span class="tabular-nums">{{ number_format($courant, 0, ',', ' ') }} {{ $ligne['suffix'] ?? '' }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-2 rounded-full {{ $accentBar }}" style="width: {{ $wCourant }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                <span>Mois précédent</span>
                                <span class="tabular-nums">{{ number_format($precedent, 0, ',', ' ') }} {{ $ligne['suffix'] ?? '' }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $wPrecedent }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<div class="grid gap-6 lg:grid-cols-3">
    @if($dashboardRole === 'secretaire_eglise')
    <div class="dashboard-panel lg:col-span-2 overflow-hidden rounded-2xl bg-white dark:bg-slate-800">
        <div class="border-b border-slate-200 bg-linear-to-r from-slate-50 to-slate-100/70 px-5 py-4 dark:border-slate-700 dark:from-slate-700/50 dark:to-slate-800/50">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Rapports membres récents</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="{{ $tableHeadTone }}">
                    <tr>
                        <th class="px-4 py-3 text-left">Période</th>
                        <th class="px-4 py-3 text-left">État</th>
                        <th class="px-4 py-3 text-right">Total membres</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
                    @forelse($rapportsMembresRecents as $r)
                    <tr class="transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-700/25">
                        <td class="px-4 py-3">{{ strtoupper((string) $r->type_periode) }} {{ $r->mois ? $r->mois.'/' : '' }}{{ $r->annee }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                {{ ucfirst((string) $r->etat) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ (int) $r->total_membres }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucun rapport membre récent.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="space-y-6">
        <div class="dashboard-panel rounded-2xl bg-white p-5 dark:bg-slate-800">
            <h3 class="mb-3 text-sm font-bold text-slate-800 dark:text-slate-100">Baptêmes récents</h3>
            <div class="space-y-2">
                @forelse($baptemesRecents as $b)
                <div class="rounded-lg border border-emerald-200/70 dark:border-emerald-800/50 bg-emerald-50/50 dark:bg-emerald-950/20 px-3 py-2">
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $b->nom }} {{ $b->prenom }}</p>
                    <p class="text-xs text-slate-500">{{ $b->date_bapteme?->translatedFormat('d/m/Y') ?? '—' }}</p>
                </div>
                @empty
                <p class="text-sm text-slate-500">Aucun baptême récent.</p>
                @endforelse
            </div>
        </div>
    </div>
    @elseif($dashboardRole === 'president_mission')
    <div class="dashboard-panel lg:col-span-2 overflow-hidden rounded-2xl bg-white dark:bg-slate-800">
        <div class="border-b border-slate-200 bg-linear-to-r from-slate-50 to-slate-100/70 px-5 py-4 dark:border-slate-700 dark:from-slate-700/50 dark:to-slate-800/50">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Rapports financiers mission récents</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="{{ $tableHeadTone }}">
                    <tr>
                        <th class="px-4 py-3 text-left">Église</th>
                        <th class="px-4 py-3 text-left">Période</th>
                        <th class="px-4 py-3 text-left">État</th>
                        <th class="px-4 py-3 text-right">À transférer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
                    @forelse($rapportsFinancesMission as $r)
                    <tr class="transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-700/25">
                        <td class="px-4 py-3">{{ $r->egliseLocale?->nom ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $r->mois }}/{{ $r->annee }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                {{ \App\Models\RapportMensuelEglise::labelsEtatsTransmission()[$r->etat_transmission] ?? $r->etat_transmission }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $r->total_a_transferer_mission_mois, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucun rapport financier récent.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="space-y-6">
        <div class="dashboard-panel rounded-2xl bg-white p-5 dark:bg-slate-800">
            <h3 class="mb-3 text-sm font-bold text-slate-800 dark:text-slate-100">Rapports membres mission</h3>
            <div class="space-y-2">
                @forelse($rapportsMembresMission as $r)
                <div class="rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-indigo-50/50 dark:bg-indigo-950/20 px-3 py-2">
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $r->egliseLocale?->nom ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ strtoupper((string) $r->type_periode) }} {{ $r->mois ? $r->mois.'/' : '' }}{{ $r->annee }} • {{ ucfirst((string) $r->etat) }}</p>
                </div>
                @empty
                <p class="text-sm text-slate-500">Aucun rapport membre récent.</p>
                @endforelse
            </div>
        </div>
    </div>
    @else
    <div class="dashboard-panel lg:col-span-2 overflow-hidden rounded-2xl bg-white dark:bg-slate-800">
        <div class="border-b border-slate-200 bg-linear-to-r from-slate-50 to-slate-100/70 px-5 py-4 dark:border-slate-700 dark:from-slate-700/50 dark:to-slate-800/50">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Derniers récaps saisis</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="{{ $tableHeadTone }}">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Église</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
                    @forelse($recapsRecents as $recap)
                    <tr class="transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-700/25">
                        <td class="px-4 py-3">{{ $recap->date_sabbat?->translatedFormat('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $recap->egliseLocale?->nom ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                                {{ ucfirst((string) $recap->statut) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) ($recap->total_dimes ?? 0) + (float) ($recap->total_offrandes ?? 0), 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucune donnée récente.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-6">
        <div class="dashboard-panel rounded-2xl bg-white p-5 dark:bg-slate-800">
            <h3 class="mb-3 text-sm font-bold text-slate-800 dark:text-slate-100">Rapports soumis à la mission</h3>
            <div class="space-y-2">
                @forelse($rapportsSoumis as $rapport)
                <div class="rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-indigo-50/50 dark:bg-indigo-950/20 px-3 py-2">
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $rapport->egliseLocale?->nom ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $rapport->mois }}/{{ $rapport->annee }} • {{ $rapport->soumis_le?->translatedFormat('d/m/Y H:i') ?? '—' }}</p>
                </div>
                @empty
                <p class="text-sm text-slate-500">Aucun rapport soumis en attente.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>

<div class="mt-6 grid gap-5 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($roleStats['cards'] as $card)
    @if(\Illuminate\Support\Facades\Route::has($card['route']))
    @php
        $cardTone = $card['color'] ?? 'green';
        $cardBlobClass = match ($cardTone) {
            'blue' => 'bg-blue-500/10 group-hover:bg-blue-500/20',
            'purple' => 'bg-violet-500/10 group-hover:bg-violet-500/20',
            'indigo' => 'bg-indigo-500/10 group-hover:bg-indigo-500/20',
            'orange' => 'bg-amber-500/10 group-hover:bg-amber-500/20',
            'red' => 'bg-rose-500/10 group-hover:bg-rose-500/20',
            'gray', 'slate' => 'bg-slate-500/10 group-hover:bg-slate-500/20',
            default => 'bg-[#00b464]/10 group-hover:bg-[#00b464]/20',
        };
        $iconRingClass = match ($cardTone) {
            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
            'purple' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
            'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
            'orange' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            'red' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
            'gray', 'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-700/70 dark:text-slate-300',
            default => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        };
    @endphp
    <a href="{{ route($card['route']) }}" class="dashboard-module-card group relative block overflow-hidden rounded-2xl bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:bg-slate-800 no-underline">
        <div class="absolute -top-10 -right-8 h-24 w-24 rounded-full transition-colors {{ $cardBlobClass }}"></div>
        <div class="relative mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl group-hover:scale-105 transition-transform {{ $iconRingClass }}">
            <x-dynamic-icon :name="$card['icon']" class="w-6 h-6" />
        </div>
        <h2 class="relative text-lg font-semibold text-slate-900 transition-colors dark:text-white {{ $accentHoverTitle }}">{{ $card['title'] }}</h2>
        <p class="relative mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $card['description'] }}</p>

        @if(!empty($card['stats']))
        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm border-t border-slate-200/80 dark:border-slate-600/60 pt-4">
            @foreach($card['stats'] as $key => $value)
            <div class="{{ count($card['stats']) === 1 ? 'col-span-2' : '' }}">
                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                <dd class="mt-0.5 text-xl font-bold tabular-nums text-slate-900 dark:text-white">{{ number_format($value) }}</dd>
            </div>
            @endforeach
        </dl>
        @endif

        <p class="relative mt-4 inline-flex items-center gap-1 text-sm font-semibold {{ $accentLink }}">
            Ouvrir
            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </p>
    </a>
    @endif
    @endforeach

</div>
@endsection
