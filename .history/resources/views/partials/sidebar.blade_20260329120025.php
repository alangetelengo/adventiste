{{-- Menu latéral : fond violet-vert, liens actifs or / violet / émeraude --}}
<aside class="sidebar theme-sidebar fixed top-20 left-0 w-[250px] h-[calc(100vh-80px)] z-[998] flex flex-col overflow-hidden transition-all duration-300">
    <nav class="flex-1 py-5 px-4 overflow-y-auto overflow-x-hidden sidebar-nav-scroll">
        <ul class="space-y-1">
            <li>
                <a href="{{ route('tableau-de-bord') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)] {{ request()->routeIs('home', 'tableau-de-bord') ? 'nav-link-active shadow-[0_0_20px_rgba(120,80,160,0.15)]' : '' }}">
                    <span class="text-lg flex-shrink-0">📊</span>
                    <span class="nav-text">Tableau de bord</span>
                </a>
            </li>
            @can('viewAny', App\Models\Membre::class)
            <li>
                <a href="{{ route('membres.index') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)] {{ request()->routeIs('membres.*') ? 'nav-link-active shadow-[0_0_20px_rgba(120,80,160,0.15)]' : '' }}">
                    <span class="text-lg flex-shrink-0">👥</span>
                    <span class="nav-text">Membres</span>
                </a>
            </li>
            @endcan
            @can('viewAny', App\Models\DepartementMinistere::class)
            <li>
                <a href="{{ route('parametres.eglises.index') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)] {{ request()->routeIs('parametres.eglises.*') ? 'nav-link-active shadow-[0_0_20px_rgba(120,80,160,0.15)]' : '' }}">
                    <span class="text-lg flex-shrink-0">📚</span>
                    <span class="nav-text">Ministères / Départements</span>
                </a>
            </li>
            @endcan
            @can('viewAny', App\Models\RecapSabbatEglise::class)
            <li>
                <a href="{{ route('finances.recaps.index') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)] {{ request()->routeIs('finances.recaps.*') ? 'nav-link-active shadow-[0_0_20px_rgba(120,80,160,0.15)]' : '' }}">
                    <span class="text-lg flex-shrink-0">💰</span>
                    <span class="nav-text">Récaps du sabbat</span>
                </a>
            </li>
            @endcan
            @can('viewAny', App\Models\RapportMensuelEglise::class)
            <li>
                <a href="{{ route('finances.rapports-mensuels.index') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)] {{ request()->routeIs('finances.rapports-mensuels.*') ? 'nav-link-active shadow-[0_0_20px_rgba(120,80,160,0.15)]' : '' }}">
                    <span class="text-lg flex-shrink-0">📑</span>
                    <span class="nav-text">Rapports mensuels</span>
                </a>
            </li>
            @endcan
            @can('viewAny', App\Models\MissionTresorerieRapportMensuel::class)
            <li>
                <a href="{{ route('finances.ventilation-tresorerie-mission.index') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)] {{ request()->routeIs('finances.ventilation-tresorerie-mission.*') ? 'nav-link-active shadow-[0_0_20px_rgba(120,80,160,0.15)]' : '' }}">
                    <span class="text-lg flex-shrink-0">📈</span>
                    <span class="nav-text">Ventilation trésorerie</span>
                </a>
            </li>
            @endcan
            <li>
                <a href="{{ route('evenements') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)] {{ request()->routeIs('evenements') ? 'nav-link-active shadow-[0_0_20px_rgba(120,80,160,0.15)]' : '' }}">
                    <span class="text-lg flex-shrink-0">📅</span>
                    <span class="nav-text">Événements</span>
                </a>
            </li>
            <li>
                <a href="{{ route('groupes') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)] {{ request()->routeIs('groupes') ? 'nav-link-active shadow-[0_0_20px_rgba(120,80,160,0.15)]' : '' }}">
                    <span class="text-lg flex-shrink-0">🙏</span>
                    <span class="nav-text">Groupes &amp; ministères</span>
                </a>
            </li>

            <li class="pt-4 mt-4 border-t border-[rgba(212,168,75,0.18)] nav-section-header">
                <p class="px-5 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider nav-text">Réglages</p>
            </li>
            <li>
                <a href="{{ route('parametres.index') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)] {{ request()->routeIs('parametres.*') ? 'nav-link-active shadow-[0_0_20px_rgba(120,80,160,0.15)]' : '' }}">
                    <span class="text-lg flex-shrink-0">⚙️</span>
                    <span class="nav-text">Paramètres</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="flex-shrink-0 p-4 border-t border-[rgba(212,168,75,0.15)] space-y-2">
        @auth
        <form method="post" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 text-white text-sm font-medium border border-white/15 hover:bg-white/15 transition-colors">
                <span>🚪</span>
                <span class="nav-text">Déconnexion</span>
            </button>
        </form>
        @endauth
    </div>
</aside>
