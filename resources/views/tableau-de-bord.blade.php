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
$uiRole = $roleStats['role_name'] ?? 'default';
$roleLabel = $roleStats['role_label'] ?? 'Utilisateur';

$theme = match ($uiRole) {
    'president_mission' => [
        'heroFrom' => 'from-violet-600/[0.12] via-indigo-600/[0.08] to-fuchsia-600/[0.10]',
        'heroRing' => 'ring-violet-500/20 dark:ring-violet-400/25',
        'pill' => 'bg-violet-500/15 text-violet-800 ring-violet-500/25 dark:bg-violet-400/10 dark:text-violet-200 dark:ring-violet-400/30',
        'glow' => 'bg-violet-400/25 dark:bg-violet-500/20',
        'kpiBar' => 'from-violet-500 to-indigo-500',
        'panelHeader' => 'from-violet-50/90 to-indigo-50/80 dark:from-violet-950/40 dark:to-indigo-950/35',
        'tableHead' => 'bg-violet-50/90 text-violet-800 dark:bg-violet-950/50 dark:text-violet-200',
        'blob' => 'bg-violet-500/10 group-hover:bg-violet-500/20',
        'btn' => 'bg-violet-600 hover:bg-violet-700 shadow-violet-600/25',
        'btnOutline' => 'border-violet-300 text-violet-800 hover:bg-violet-50 dark:border-violet-600/50 dark:text-violet-200 dark:hover:bg-violet-950/40',
        'link' => 'text-violet-600 dark:text-violet-300',
        'titleHover' => 'group-hover:text-violet-600 dark:group-hover:text-violet-300',
        'bar' => 'bg-violet-500',
        'tagline' => 'Pilotage stratégique, finances consolidées et suivi des églises.',
    ],
    'secretaire_eglise' => [
        'heroFrom' => 'from-sky-600/[0.10] via-cyan-600/[0.08] to-blue-600/[0.10]',
        'heroRing' => 'ring-sky-500/20 dark:ring-sky-400/25',
        'pill' => 'bg-sky-500/15 text-sky-900 ring-sky-500/25 dark:bg-sky-400/10 dark:text-sky-100 dark:ring-sky-400/30',
        'glow' => 'bg-sky-400/20 dark:bg-sky-500/15',
        'kpiBar' => 'from-sky-500 to-cyan-500',
        'panelHeader' => 'from-sky-50/90 to-cyan-50/70 dark:from-sky-950/40 dark:to-cyan-950/30',
        'tableHead' => 'bg-sky-50/90 text-sky-800 dark:bg-sky-950/50 dark:text-sky-200',
        'blob' => 'bg-sky-500/10 group-hover:bg-sky-500/20',
        'btn' => 'bg-sky-600 hover:bg-sky-700 shadow-sky-600/25',
        'btnOutline' => 'border-sky-300 text-sky-900 hover:bg-sky-50 dark:border-sky-600/50 dark:text-sky-200 dark:hover:bg-sky-950/40',
        'link' => 'text-sky-600 dark:text-sky-300',
        'titleHover' => 'group-hover:text-sky-600 dark:group-hover:text-sky-300',
        'bar' => 'bg-sky-500',
        'tagline' => 'Membres, baptêmes et rapports membres : secrétariat de l’église locale.',
    ],
    'tresorier_eglise' => [
        'heroFrom' => 'from-emerald-600/[0.11] via-teal-600/[0.08] to-cyan-600/[0.08]',
        'heroRing' => 'ring-emerald-500/25 dark:ring-emerald-400/25',
        'pill' => 'bg-emerald-500/15 text-emerald-900 ring-emerald-500/30 dark:bg-emerald-400/10 dark:text-emerald-100 dark:ring-emerald-400/30',
        'glow' => 'bg-emerald-400/25 dark:bg-emerald-500/15',
        'kpiBar' => 'from-emerald-500 to-teal-500',
        'panelHeader' => 'from-emerald-50/90 to-teal-50/70 dark:from-emerald-950/40 dark:to-teal-950/30',
        'tableHead' => 'bg-emerald-50/90 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200',
        'blob' => 'bg-[#00b464]/12 group-hover:bg-[#00b464]/22',
        'btn' => 'bg-[#00b464] hover:bg-[#009a55] shadow-emerald-600/20',
        'btnOutline' => 'border-emerald-300 text-emerald-900 hover:bg-emerald-50 dark:border-emerald-600/50 dark:text-emerald-200 dark:hover:bg-emerald-950/40',
        'link' => 'text-[#00b464] dark:text-emerald-400',
        'titleHover' => 'group-hover:text-[#00b464] dark:group-hover:text-emerald-300',
        'bar' => 'bg-[#00b464]',
        'tagline' => 'Récaps hebdomadaires, rapports mensuels et soumission mission : votre périmètre trésorerie locale.',
    ],
    'tresorier_mission' => [
        'heroFrom' => 'from-teal-600/[0.10] via-emerald-600/[0.09] to-green-600/[0.08]',
        'heroRing' => 'ring-teal-500/25 dark:ring-teal-400/25',
        'pill' => 'bg-teal-500/15 text-teal-900 ring-teal-500/30 dark:bg-teal-400/10 dark:text-teal-100 dark:ring-teal-400/30',
        'glow' => 'bg-teal-400/20 dark:bg-teal-500/15',
        'kpiBar' => 'from-teal-500 to-emerald-500',
        'panelHeader' => 'from-teal-50/90 to-emerald-50/70 dark:from-teal-950/40 dark:to-emerald-950/30',
        'tableHead' => 'bg-teal-50/90 text-teal-900 dark:bg-teal-950/50 dark:text-teal-200',
        'blob' => 'bg-teal-500/10 group-hover:bg-teal-500/20',
        'btn' => 'bg-teal-600 hover:bg-teal-700 shadow-teal-600/25',
        'btnOutline' => 'border-teal-300 text-teal-900 hover:bg-teal-50 dark:border-teal-600/50 dark:text-teal-200 dark:hover:bg-teal-950/40',
        'link' => 'text-teal-600 dark:text-teal-300',
        'titleHover' => 'group-hover:text-teal-600 dark:group-hover:text-teal-300',
        'bar' => 'bg-teal-500',
        'tagline' => 'Consolidation financière, validation des récaps et groupes mission.',
    ],
    'secretaire_executif_mission' => [
        'heroFrom' => 'from-indigo-600/[0.10] via-blue-600/[0.08] to-slate-600/[0.06]',
        'heroRing' => 'ring-indigo-500/20 dark:ring-indigo-400/25',
        'pill' => 'bg-indigo-500/15 text-indigo-900 ring-indigo-500/25 dark:bg-indigo-400/10 dark:text-indigo-100 dark:ring-indigo-400/30',
        'glow' => 'bg-indigo-400/20 dark:bg-indigo-500/15',
        'kpiBar' => 'from-indigo-500 to-blue-500',
        'panelHeader' => 'from-indigo-50/90 to-blue-50/70 dark:from-indigo-950/40 dark:to-blue-950/30',
        'tableHead' => 'bg-indigo-50/90 text-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-200',
        'blob' => 'bg-indigo-500/10 group-hover:bg-indigo-500/20',
        'btn' => 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/25',
        'btnOutline' => 'border-indigo-300 text-indigo-900 hover:bg-indigo-50 dark:border-indigo-600/50 dark:text-indigo-200 dark:hover:bg-indigo-950/40',
        'link' => 'text-indigo-600 dark:text-indigo-300',
        'titleHover' => 'group-hover:text-indigo-600 dark:group-hover:text-indigo-300',
        'bar' => 'bg-indigo-500',
        'tagline' => 'Vue mission : églises, membres et rapports membres par paroisse.',
    ],
    'admin_mission' => [
        'heroFrom' => 'from-rose-600/[0.08] via-amber-600/[0.08] to-slate-700/[0.08]',
        'heroRing' => 'ring-amber-500/25 dark:ring-amber-400/20',
        'pill' => 'bg-amber-500/15 text-amber-950 ring-amber-500/30 dark:bg-amber-400/10 dark:text-amber-100 dark:ring-amber-400/30',
        'glow' => 'bg-amber-400/20 dark:bg-amber-500/10',
        'kpiBar' => 'from-amber-500 to-rose-500',
        'panelHeader' => 'from-amber-50/90 to-rose-50/50 dark:from-amber-950/35 dark:to-rose-950/25',
        'tableHead' => 'bg-amber-50/90 text-amber-950 dark:bg-amber-950/45 dark:text-amber-100',
        'blob' => 'bg-amber-500/10 group-hover:bg-amber-500/20',
        'btn' => 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/25 text-white',
        'btnOutline' => 'border-amber-400 text-amber-950 hover:bg-amber-50 dark:border-amber-600/50 dark:text-amber-100 dark:hover:bg-amber-950/40',
        'link' => 'text-amber-700 dark:text-amber-300',
        'titleHover' => 'group-hover:text-amber-700 dark:group-hover:text-amber-300',
        'bar' => 'bg-amber-500',
        'tagline' => 'Administration complète : utilisateurs, permissions, structure et finances.',
    ],
    default => [
        'heroFrom' => 'from-slate-600/[0.08] via-emerald-600/[0.07] to-slate-500/[0.06]',
        'heroRing' => 'ring-slate-400/25 dark:ring-slate-500/30',
        'pill' => 'bg-slate-500/12 text-slate-800 ring-slate-400/30 dark:bg-slate-500/20 dark:text-slate-200 dark:ring-slate-500/40',
        'glow' => 'bg-emerald-400/15 dark:bg-emerald-500/10',
        'kpiBar' => 'from-slate-500 to-emerald-500',
        'panelHeader' => 'from-slate-50/90 to-slate-100/80 dark:from-slate-800/50 dark:to-slate-900/50',
        'tableHead' => 'bg-slate-100/90 text-slate-700 dark:bg-slate-900/60 dark:text-slate-200',
        'blob' => 'bg-[#00b464]/10 group-hover:bg-[#00b464]/20',
        'btn' => 'bg-[#00b464] hover:bg-[#009a55] shadow-emerald-600/20',
        'btnOutline' => 'border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800/60',
        'link' => 'text-[#00b464] dark:text-emerald-400',
        'titleHover' => 'group-hover:text-[#00b464] dark:group-hover:text-emerald-300',
        'bar' => 'bg-[#00b464]',
        'tagline' => 'Vos indicateurs et raccourcis selon votre périmètre.',
    ],
};

$panelHeaderTone = $theme['panelHeader'];
$tableHeadTone = $theme['tableHead'];
$accentBlob = $theme['blob'];
$accentButton = $theme['btn'];
$accentHoverTitle = $theme['titleHover'];
$accentLink = $theme['link'];
$accentBar = $theme['bar'];
@endphp

<div class="space-y-8 sm:space-y-10">
    {{-- Bandeau d’accueil (identité visuelle par rôle) --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-linear-to-br {{ $theme['heroFrom'] }} dark:border-slate-700/80 ring-1 {{ $theme['heroRing'] }} shadow-sm">
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full blur-3xl {{ $theme['glow'] }} opacity-60"></div>
        <div class="pointer-events-none absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-slate-400/10 blur-3xl dark:bg-slate-600/10"></div>
        <div class="relative flex flex-col gap-5 p-6 sm:flex-row sm:items-end sm:justify-between sm:p-8">
            <div class="min-w-0 space-y-3">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold tracking-wide ring-1 {{ $theme['pill'] }}">
                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-80" aria-hidden="true"></span>
                    {{ $roleLabel }}
                </span>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                        Bonjour{{ auth()->check() && auth()->user()->name ? ', '.explode(' ', auth()->user()->name)[0] : '' }}
                    </h2>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        {{ $theme['tagline'] }}
                    </p>
                </div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-500">
                    Période de référence · <span class="text-slate-700 dark:text-slate-300 normal-case font-semibold">{{ $moisCourant }}</span>
                </p>
            </div>
            @php
                $u = auth()->user();
                $heroEglises = ($stats['eglises_actives'] ?? null) !== null && $u && $u->can('viewAny', App\Models\EgliseLocale::class);
                $heroMembres = ($stats['membres_total'] ?? 0) > 0 && $u && $u->can('viewAny', App\Models\Membre::class);
                $heroRecaps = ($stats['recaps_total'] ?? 0) > 0 && $u && $u->can('viewAny', App\Models\RecapSabbatEglise::class);
            @endphp
            @if ($heroEglises || $heroMembres || $heroRecaps)
            <div class="flex flex-wrap gap-2 sm:max-w-md sm:justify-end">
                @if ($heroEglises)
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-white/40 bg-white/55 px-3 py-2 text-xs font-semibold text-slate-800 shadow-sm backdrop-blur-sm dark:border-slate-600/50 dark:bg-slate-900/40 dark:text-slate-100">
                    <span class="tabular-nums text-sm">{{ number_format((int) $stats['eglises_actives'], 0, ',', ' ') }}</span>
                    <span class="font-normal opacity-80">églises actives</span>
                </span>
                @endif
                @if ($heroMembres)
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-white/40 bg-white/55 px-3 py-2 text-xs font-semibold text-slate-800 shadow-sm backdrop-blur-sm dark:border-slate-600/50 dark:bg-slate-900/40 dark:text-slate-100">
                    <span class="tabular-nums text-sm">{{ number_format((int) $stats['membres_total'], 0, ',', ' ') }}</span>
                    <span class="font-normal opacity-80">membres</span>
                </span>
                @endif
                @if ($heroRecaps)
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-white/40 bg-white/55 px-3 py-2 text-xs font-semibold text-slate-800 shadow-sm backdrop-blur-sm dark:border-slate-600/50 dark:bg-slate-900/40 dark:text-slate-100">
                    <span class="tabular-nums text-sm">{{ number_format((int) $stats['recaps_total'], 0, ',', ' ') }}</span>
                    <span class="font-normal opacity-80">récaps (total)</span>
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- KPIs --}}
    <div>
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Indicateurs clés</h3>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Données calculées pour votre périmètre</p>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @forelse($kpis as $kpi)
            <div class="adventiste-card-pro-static group relative overflow-hidden rounded-xl border border-slate-200/80 p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700/80 dark:hover:shadow-black/30">
                <div class="absolute inset-x-0 top-0 h-1 bg-linear-to-r {{ $theme['kpiBar'] }} opacity-90"></div>
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full opacity-50 transition-colors {{ $accentBlob }}"></div>
                <p class="relative text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $kpi['label'] ?? 'Indicateur' }}</p>
                <p class="relative mt-3 text-2xl font-bold tabular-nums tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ number_format((float) ($kpi['value'] ?? 0), 0, ',', ' ') }}</p>
                @if (!empty($kpi['suffix']))
                <p class="relative mt-1.5 inline-flex items-center rounded-md bg-slate-100/90 px-2 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-800/80 dark:text-slate-300">{{ $kpi['suffix'] }}</p>
                @endif
            </div>
            @empty
            <div class="adventiste-card-pro-static rounded-xl p-6 sm:col-span-2 xl:col-span-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Aucun indicateur disponible pour ce rôle.</p>
            </div>
            @endforelse
        </div>
    </div>

    @if(!empty($roleStats['actions']))
    <div class="adventiste-card-pro-static overflow-hidden rounded-xl">
        <div class="flex flex-col gap-1 border-b border-slate-200/90 bg-linear-to-r px-5 py-4 dark:border-slate-700/80 sm:flex-row sm:items-center sm:justify-between {{ $panelHeaderTone }}">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Actions rapides</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400">Raccourcis vers les tâches fréquentes</p>
            </div>
        </div>
        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 sm:p-5">
            @foreach($roleStats['actions'] as $action)
            @if(\Illuminate\Support\Facades\Route::has($action['route']) && \App\Support\NavigationGate::canVisitRoute(auth()->user(), $action['route']))
            <a href="{{ route($action['route']) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-transparent px-3 py-2.5 text-sm font-semibold text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg no-underline {{ $accentButton }}">
                <x-dynamic-icon :name="$action['icon']" class="w-4 h-4 shrink-0 opacity-95" />
                {{ $action['label'] }}
            </a>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    @if($priorites->isNotEmpty())
    <div class="adventiste-card-pro-static overflow-hidden rounded-xl">
        <div class="border-b border-slate-200/90 bg-linear-to-r px-5 py-4 dark:border-slate-700/80 {{ $panelHeaderTone }}">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Priorités à traiter</h3>
            <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Classées par urgence relative</p>
        </div>
        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 sm:p-5">
            @foreach($priorites as $item)
                @php
                    $tone = $item['tone'] ?? 'slate';
                    $toneClass = match ($tone) {
                        'rose' => 'border-rose-200 bg-rose-50/80 text-rose-900 dark:border-rose-800/50 dark:bg-rose-950/25 dark:text-rose-100',
                        'amber' => 'border-amber-200 bg-amber-50/80 text-amber-950 dark:border-amber-800/50 dark:bg-amber-950/25 dark:text-amber-100',
                        'indigo' => 'border-indigo-200 bg-indigo-50/80 text-indigo-900 dark:border-indigo-800/50 dark:bg-indigo-950/25 dark:text-indigo-100',
                        default => 'border-slate-200 bg-slate-50/80 text-slate-800 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-100',
                    };
                    $stripe = match ($tone) {
                        'rose' => 'bg-rose-500',
                        'amber' => 'bg-amber-500',
                        'indigo' => 'bg-indigo-500',
                        default => 'bg-slate-400',
                    };
                @endphp
                <div class="relative overflow-hidden rounded-xl border p-4 pl-5 shadow-sm {{ $toneClass }}">
                    <span class="absolute left-0 top-0 h-full w-1 {{ $stripe }}" aria-hidden="true"></span>
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/80 shadow-sm dark:bg-slate-900/50">
                            <x-dynamic-icon :name="$item['icon'] ?? 'chart-bar'" class="h-5 w-5" />
                        </div>
                        <span class="inline-flex items-center rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 ring-black/5 dark:bg-slate-900/60 dark:ring-white/10">
                            {{ ((int) ($item['value'] ?? 0)) > 0 ? 'À traiter' : 'OK' }}
                        </span>
                    </div>
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ $item['label'] ?? 'Priorité' }}</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums text-slate-900 dark:text-white">{{ number_format((int) ($item['value'] ?? 0), 0, ',', ' ') }}</p>
                    <p class="mt-2 text-xs leading-relaxed opacity-90">{{ $item['help'] ?? '' }}</p>
                    @if(!empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']) && \App\Support\NavigationGate::canVisitRoute(auth()->user(), $item['route']))
                        <a href="{{ route($item['route']) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-bold no-underline {{ $accentLink }}">
                            Accéder
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
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
    <div class="adventiste-card-pro-static overflow-hidden rounded-xl">
        <div class="border-b border-slate-200/90 bg-linear-to-r px-5 py-4 dark:border-slate-700/80 {{ $panelHeaderTone }}">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Comparatif mensuel</h3>
            <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Mois courant vs mois précédent</p>
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
                <div class="rounded-xl border border-slate-200/90 bg-slate-50/80 p-5 shadow-inner dark:border-slate-700 dark:bg-slate-900/40">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $ligne['label'] ?? 'Indicateur' }}</p>
                        <p class="text-xs font-semibold tabular-nums {{ $delta >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 0, ',', ' ') }} {{ $ligne['suffix'] ?? '' }}
                        </p>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <div class="mb-1.5 flex items-center justify-between text-xs font-medium text-slate-500 dark:text-slate-400">
                                <span>Mois courant</span>
                                <span class="tabular-nums text-slate-700 dark:text-slate-200">{{ number_format($courant, 0, ',', ' ') }} {{ $ligne['suffix'] ?? '' }}</span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-linear-to-r {{ $theme['kpiBar'] }}" style="width: {{ $wCourant }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1.5 flex items-center justify-between text-xs font-medium text-slate-500 dark:text-slate-400">
                                <span>Mois précédent</span>
                                <span class="tabular-nums text-slate-700 dark:text-slate-200">{{ number_format($precedent, 0, ',', ' ') }} {{ $ligne['suffix'] ?? '' }}</span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-indigo-500/90" style="width: {{ $wPrecedent }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-3 lg:gap-8">
    @if($dashboardRole === 'secretaire_eglise')
    @can('viewAny', App\Models\RapportMembreEglise::class)
    <div class="adventiste-card-pro-static lg:col-span-2 overflow-hidden rounded-xl">
        <div class="border-b border-slate-200/90 bg-linear-to-r px-5 py-4 dark:border-slate-700/80 {{ $panelHeaderTone }}">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Rapports membres récents</h3>
            <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Derniers enregistrements</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="{{ $tableHeadTone }}">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Période</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider">État</th>
                        <th class="px-4 py-3.5 text-right text-xs font-bold uppercase tracking-wider">Membres</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($rapportsMembresRecents as $r)
                    <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ strtoupper((string) $r->type_periode) }} {{ $r->mois ? $r->mois.'/' : '' }}{{ $r->annee }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                {{ ucfirst((string) $r->etat) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-medium text-slate-900 dark:text-white">{{ (int) $r->total_membres }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-12 text-center text-sm text-slate-500">Aucun rapport membre récent.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endcan
    @can('viewAny', App\Models\Bapteme::class)
    <div class="space-y-6">
        <div class="adventiste-card-pro-static rounded-xl p-5 sm:p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Baptêmes récents</h3>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Derniers enregistrements</p>
            <div class="mt-4 space-y-3">
                @forelse($baptemesRecents as $b)
                <div class="flex items-center gap-3 rounded-xl border border-emerald-200/80 bg-linear-to-r from-emerald-50/90 to-white px-3 py-3 dark:border-emerald-900/40 dark:from-emerald-950/30 dark:to-slate-900/20">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-sm font-bold text-emerald-800 dark:text-emerald-200">{{ mb_strtoupper(mb_substr((string) ($b->prenom ?? '?'), 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $b->nom }} {{ $b->prenom }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $b->date_bapteme?->translatedFormat('d M. Y') ?? '—' }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">Aucun baptême récent.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endcan
    @elseif($dashboardRole === 'president_mission' || $dashboardRole === 'secretaire_executif_mission')
    @can('viewAny', App\Models\RapportMensuelEglise::class)
    <div class="adventiste-card-pro-static lg:col-span-2 overflow-hidden rounded-xl">
        <div class="border-b border-slate-200/90 bg-linear-to-r px-5 py-4 dark:border-slate-700/80 {{ $panelHeaderTone }}">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $dashboardRole === 'secretaire_executif_mission' ? 'Rapports mensuels (églises)' : 'Rapports financiers (mission)' }}</h3>
            <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">{{ $dashboardRole === 'secretaire_executif_mission' ? 'Dernières synthèses par paroisse (consultation)' : 'Derniers mouvements par église' }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="{{ $tableHeadTone }}">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Église</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Période</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider">État</th>
                        <th class="px-4 py-3.5 text-right text-xs font-bold uppercase tracking-wider">À transférer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($rapportsFinancesMission as $r)
                    <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $r->egliseLocale?->nom ?? '—' }}</td>
                        <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-slate-300">{{ $r->mois }}/{{ $r->annee }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                {{ \App\Models\RapportMensuelEglise::labelsEtatsTransmission()[$r->etat_transmission] ?? $r->etat_transmission }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-slate-900 dark:text-white">{{ number_format((float) $r->total_a_transferer_mission_mois, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-500">FCFA</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-sm text-slate-500">Aucun rapport financier récent.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endcan
    @can('viewAny', App\Models\RapportMembreEglise::class)
    <div class="space-y-6">
        <div class="adventiste-card-pro-static rounded-xl p-5 sm:p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Rapports membres</h3>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Vue consolidée mission</p>
            <div class="mt-4 space-y-3">
                @forelse($rapportsMembresMission as $r)
                <div class="rounded-xl border border-indigo-200/80 bg-linear-to-br from-indigo-50/80 to-white px-3 py-3 dark:border-indigo-900/45 dark:from-indigo-950/35 dark:to-slate-900/20">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $r->egliseLocale?->nom ?? '—' }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ strtoupper((string) $r->type_periode) }} {{ $r->mois ? $r->mois.'/' : '' }}{{ $r->annee }} · {{ ucfirst((string) $r->etat) }}</p>
                </div>
                @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">Aucun rapport membre récent.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endcan
    @else
    @if(\App\Support\NavigationGate::peutVoirRecapsSabbatDansNavigation(auth()->user()))
    @can('viewAny', App\Models\RecapSabbatEglise::class)
    <div class="adventiste-card-pro-static lg:col-span-2 overflow-hidden rounded-xl">
        <div class="border-b border-slate-200/90 bg-linear-to-r px-5 py-4 dark:border-slate-700/80 {{ $panelHeaderTone }}">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Derniers récaps du sabbat</h3>
            <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">Saisie et statut</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="{{ $tableHeadTone }}">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Église</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3.5 text-right text-xs font-bold uppercase tracking-wider">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($recapsRecents as $recap)
                    <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-medium tabular-nums text-slate-800 dark:text-slate-100">{{ $recap->date_sabbat?->translatedFormat('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $recap->egliseLocale?->nom ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                {{ $recap->statut }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-slate-900 dark:text-white">{{ number_format((float) ($recap->total_dimes ?? 0) + (float) ($recap->total_offrandes ?? 0), 0, ',', ' ') }} <span class="text-xs font-normal text-slate-500">FCFA</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-sm text-slate-500">Aucune donnée récente.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endcan
    @endif

    @can('viewAny', App\Models\RapportMensuelEglise::class)
    <div class="space-y-6">
        <div class="adventiste-card-pro-static rounded-xl p-5 sm:p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Rapports mensuels soumis</h3>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->hasRole('tresorier_mission') || auth()->user()->hasRole('president_mission') || auth()->user()->hasRole('admin_mission') ? 'En attente de validation mission' : 'Suivi des transmissions' }}</p>
            <div class="mt-4 space-y-3">
                @forelse($rapportsSoumis as $rapport)
                <div class="rounded-xl border border-indigo-200/80 bg-linear-to-br from-indigo-50/80 to-white px-3 py-3 dark:border-indigo-900/45 dark:from-indigo-950/35 dark:to-slate-900/20">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $rapport->egliseLocale?->nom ?? '—' }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ $rapport->mois }}/{{ $rapport->annee }} · {{ $rapport->soumis_le?->translatedFormat('d/m/Y H:i') ?? '—' }}</p>
                </div>
                @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">Aucun rapport soumis en attente.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endcan
    @endif
</div>

<div class="mt-8 sm:mt-10">
    <div class="mb-4">
        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Modules</h3>
        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Accès direct selon votre rôle</p>
    </div>
    @php
        $cardsDashboard = collect($roleStats['cards'] ?? [])->filter(function ($card) {
            $r = $card['route'] ?? '';
            return $r !== '' && \Illuminate\Support\Facades\Route::has($r) && \App\Support\NavigationGate::canVisitRoute(auth()->user(), $r);
        });
    @endphp
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($cardsDashboard as $card)
        @php
            $cardTone = $card['color'] ?? 'green';
            $cardBlobClass = match ($cardTone) {
                'blue' => 'bg-blue-500/10 group-hover:bg-blue-500/20',
                'purple' => 'bg-violet-500/10 group-hover:bg-violet-500/20',
                'indigo' => 'bg-indigo-500/10 group-hover:bg-indigo-500/20',
                'orange' => 'bg-amber-500/10 group-hover:bg-amber-500/20',
                'red' => 'bg-rose-500/10 group-hover:bg-rose-500/20',
                'gray', 'slate' => 'bg-slate-500/10 group-hover:bg-slate-500/20',
                'emerald' => 'bg-emerald-500/10 group-hover:bg-emerald-500/20',
                default => 'bg-[#00b464]/10 group-hover:bg-[#00b464]/20',
            };
            $iconRingClass = match ($cardTone) {
                'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                'purple' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
                'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                'orange' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                'red' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
                'gray', 'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-700/70 dark:text-slate-300',
                'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                default => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
            };
        @endphp
        <a href="{{ route($card['route']) }}" class="adventiste-card-pro group relative flex flex-col overflow-hidden rounded-xl p-6 no-underline">
            <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full transition-colors duration-300 {{ $cardBlobClass }}"></div>
            <div class="relative mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl shadow-sm ring-1 ring-black/5 transition-transform duration-300 group-hover:scale-105 dark:ring-white/10 {{ $iconRingClass }}">
                <x-dynamic-icon :name="$card['icon']" class="w-6 h-6" />
            </div>
            <h2 class="relative text-lg font-bold text-slate-900 transition-colors dark:text-white {{ $accentHoverTitle }}">{{ $card['title'] }}</h2>
            <p class="relative mt-2 flex-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $card['description'] }}</p>

            @if(!empty($card['stats']))
            <dl class="relative mt-5 grid grid-cols-2 gap-3 border-t border-slate-200/90 pt-4 text-sm dark:border-slate-600/60">
                @foreach($card['stats'] as $key => $value)
                <div class="{{ count($card['stats']) === 1 ? 'col-span-2' : '' }}">
                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                    <dd class="mt-1 text-xl font-bold tabular-nums text-slate-900 dark:text-white">{{ number_format((int) $value) }}</dd>
                </div>
                @endforeach
            </dl>
            @endif

            <p class="relative mt-5 inline-flex items-center gap-1.5 text-sm font-bold {{ $accentLink }}">
                Ouvrir le module
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </p>
        </a>
        @empty
        <p class="text-sm text-slate-500 dark:text-slate-400 sm:col-span-2 lg:col-span-3">Aucun module supplémentaire n’est accessible avec vos droits actuels.</p>
        @endforelse
    </div>
</div>
@endsection
