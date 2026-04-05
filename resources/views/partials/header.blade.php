{{-- Header : hamburger + actions utilisateur (style GED) --}}
<div id="mainHeader" class="header theme-header-bar fixed top-0 left-[250px] right-0 h-20 z-1099 flex items-center text-white transition-all duration-300"
     style="position: fixed; top: 0; left: 250px; right: 0; height: 80px; display: flex; z-index: 2147482000; background:
        radial-gradient(ellipse 80% 80% at 20% 80%, rgba(0, 180, 100, 0.25), rgba(0, 180, 100, 0.12) 25%, transparent 50%),
        linear-gradient(135deg, #0a0f15 0%, #0d1a1a 25%, #0f2520 50%, #0d1a1a 75%, #0a0f15 100%); border: 0 !important; border-bottom: 0 !important; box-shadow: none !important; outline: 0 !important;">
    <button type="button" id="navControl" class="nav-control shrink-0 h-full w-14 flex items-center justify-center hover:bg-slate-100/80 dark:hover:bg-white/5 focus:bg-transparent focus:outline-none focus:ring-0 active:bg-slate-100/80 dark:active:bg-white/5 cursor-pointer transition-all duration-300" title="{{ __('ui.nav_menu') }}">
        <div class="hamburger flex flex-col gap-1.5 w-6 items-center justify-center">
            <span class="line block w-full h-0.5 rounded bg-linear-to-r from-church-gold via-[#00c978] to-[#8b6cb8] transition-all duration-300"></span>
            <span class="line block w-full h-0.5 rounded bg-linear-to-r from-church-gold via-[#00c978] to-[#8b6cb8] transition-all duration-300"></span>
            <span class="line block w-full h-0.5 rounded bg-linear-to-r from-church-gold via-[#00c978] to-[#8b6cb8] transition-all duration-300"></span>
        </div>
    </button>
    <div class="flex-1 flex justify-between items-center px-6 min-w-0">
        <span class="text-sm font-semibold system-label truncate text-white/90">
            <span class="text-emerald-100/90">
                {{ config('app.name') }} — {{ config('app.denomination') }}
                @auth
                    @if (auth()->user()->egliseLocale?->nom)
                        — {{ mb_strtoupper(auth()->user()->egliseLocale->nom) }}
                    @elseif (auth()->user()->mission?->nom)
                        — {{ auth()->user()->mission->nom }}
                    @endif
                @endauth
            </span>
        </span>
        <ul class="header-right flex items-center gap-1 shrink-0">
            <li class="mr-2 flex items-center">
                @include('partials.locale-switcher', ['tone' => 'dark'])
            </li>
            <li class="mr-3">
                <button id="themeToggle" type="button" class="px-3 py-1.5 rounded bg-white/10 text-lg hover:bg-white/20 transition-colors" title="{{ __('ui.theme_toggle') }}">🌙</button>
            </li>
            @auth
            <li class="mr-3">
                <button id="notifToggle" type="button" class="relative flex items-center gap-1.5 px-3 py-2 rounded-xl hover:bg-white/10 transition-colors" title="{{ __('ui.notifications') }}" aria-haspopup="true" aria-expanded="false">
                    <span class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center">
                        <span class="text-[1.35rem] leading-none drop-shadow-[0_1px_2px_rgba(0,0,0,0.35)]" aria-hidden="true">🔔</span>
                        <span id="notifBadge" class="notif-count-badge hidden" role="status" aria-live="polite" aria-atomic="true"></span>
                    </span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-white/50">▼</span>
                </button>
            </li>
            <li class="flex items-center user-box">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=4a3570&color=f0c85c" alt="Avatar" class="w-10 h-10 rounded-full avatar ring-2 ring-church-gold/40" width="40" height="40">
                <div class="user-details ml-2">
                    <p class="user-name text-sm font-semibold text-white">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <button id="profileToggle" type="button" class="ml-2 text-white/80 hover:text-white cursor-pointer text-sm" aria-haspopup="true" aria-expanded="false">▼</button>
            </li>
            @else
            <li>
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg bg-[#00b464] text-white font-semibold text-sm hover:bg-[#00a055] transition-colors">{{ __('ui.nav.login') }}</a>
            </li>
            @endauth
        </ul>
    </div>
</div>

@auth
<div id="notifMenu" class="menu-hidden notif-dropdown-panel overflow-hidden">
    <div class="notif-dropdown-header">
        <span class="notif-dropdown-title">{{ __('ui.notifications') }}</span>
    </div>
    <div id="notifBody" class="notif-dropdown-body">
        <div id="notifLoading" class="notif-loading">
            <span class="notif-spinner"></span>
            <span>{{ __('ui.notifications.loading') }}</span>
        </div>
        <div id="notifList" class="menu-hidden"></div>
        <div id="notifEmpty" class="notif-empty menu-hidden">
            <span class="notif-empty-icon">✓</span>
            <p>{{ __('ui.notifications.empty') }}</p>
        </div>
    </div>
    <div id="notifFooter" class="notif-dropdown-footer menu-hidden">
        <form method="POST" action="{{ route('notifications.read-all') }}" class="m-0">
            @csrf
            <button id="notifFooterMarkAll" type="submit" class="notif-footer-link w-full">{{ __('ui.notifications.mark_all') }}</button>
        </form>
        <a id="notifFooterAll" href="{{ route('notifications.index') }}" class="notif-footer-link">{{ __('ui.notifications.view_all') }}</a>
    </div>
</div>

<div id="profileMenu" class="menu-hidden header-dropdown w-48 bg-white rounded-lg shadow-xl py-1 border border-slate-200 min-w-48">
    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">👤 {{ __('ui.profile') }}</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 font-medium">🔑 {{ __('ui.logout') }}</button>
    </form>
</div>
@endauth

<style>
.header-right { list-style: none; margin: 0; padding: 0; }
.user-box {
    display: flex; align-items: center; gap: 8px;
    background: rgba(0,0,0,0.2); padding: 6px 12px; border-radius: 8px;
}
.user-details .user-name { font-size: 0.95rem; font-weight: 600; color: #fff; line-height: 1.2; }
.user-details .user-email { font-size: 0.75rem; color: rgba(255,255,255,0.9); }
.header-dropdown a, .header-dropdown button { transition: background 0.15s; }
.menu-hidden { display: none !important; }

#notifMenu,
#profileMenu {
    position: fixed !important;
    z-index: 2147483000 !important;
    opacity: 1 !important;
    filter: none !important;
    isolation: isolate;
}

.notif-dropdown-panel {
    min-width: 380px; max-width: 420px; width: 380px;
    padding: 0; background: #fff; border-radius: 12px;
    border: 1px solid rgba(0,180,100,0.2);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15), 0 0 1px rgba(0,0,0,0.1);
}
.notif-dropdown-header {
    background: linear-gradient(135deg, rgba(0,180,100,0.12), rgba(0,150,85,0.08));
    padding: 12px 16px; border-bottom: 1px solid rgba(0,180,100,0.15);
}
.notif-dropdown-title { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
.notif-dropdown-body { max-height: 320px; overflow-y: auto; padding: 8px 0; background: #fff; }
.notif-list { display: flex; flex-direction: column; gap: 0; }
.notif-item {
    display: block; text-decoration: none; color: #0f172a;
    padding: 10px 14px; border-bottom: 1px solid #f1f5f9;
}
.notif-item:hover { background: #f8fafc; }
.notif-item-title { display: block; font-size: 0.88rem; font-weight: 700; line-height: 1.25; }
.notif-item-message { display: block; margin-top: 2px; font-size: 0.8rem; color: #475569; line-height: 1.3; }
.notif-item-time { display: block; margin-top: 4px; font-size: 0.72rem; color: #64748b; }
.notif-loading {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 24px 16px; color: #64748b; font-size: 0.875rem;
}
.notif-spinner {
    width: 18px; height: 18px; border: 2px solid #e2e8f0;
    border-top-color: #00b464; border-radius: 50%;
    animation: notif-spin 0.7s linear infinite;
}
@keyframes notif-spin { to { transform: rotate(360deg); } }
.notif-dropdown-footer { padding: 10px 16px; background: #f8fafc; border-top: 1px solid #e2e8f0; }
.notif-footer-link {
    display: block; text-align: center; color: #059669 !important;
    font-weight: 600; font-size: 0.875rem; text-decoration: none !important;
    padding: 6px 0; border-radius: 6px; transition: background 0.2s, color 0.2s;
}
.notif-footer-link:hover { background: rgba(0,180,100,0.08); color: #047857 !important; }
.notif-empty { text-align: center; padding: 32px 20px; color: #94a3b8; }
.notif-empty-icon {
    display: flex; align-items: center; justify-content: center;
    width: 48px; height: 48px; margin: 0 auto 12px;
    border-radius: 50%; background: #f1f5f9; color: #94a3b8; font-size: 1.25rem;
}
.notif-empty p { margin: 0; font-size: 0.9rem; }

.notif-count-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    z-index: 10;
    box-sizing: border-box;
    min-height: 1.125rem;
    min-width: 1.125rem;
    padding: 0 5px;
    border-radius: 9999px;
    border: 1px solid rgba(255, 255, 255, 0.45);
    background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 100%);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4), 0 0 0 2px #0a0f15;
    font-size: 10px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    line-height: 1;
    letter-spacing: -0.02em;
    color: #dc2626;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.notif-count-badge.is-on {
    display: flex;
}
.notif-count-badge.notif-count-badge--2 {
    min-width: 1.35rem;
    padding: 0 5px;
    font-size: 9px;
}
.notif-count-badge.notif-count-badge--3 {
    min-width: 1.65rem;
    padding: 0 4px;
    font-size: 8px;
}

.header .nav-control .hamburger.is-active .line:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.header .nav-control .hamburger.is-active .line:nth-child(2) { opacity: 0; }
.header .nav-control .hamburger.is-active .line:nth-child(3) { transform: rotate(-45deg) translate(6px, -6px); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const notifToggle = document.getElementById('notifToggle');
    const notifMenu = document.getElementById('notifMenu');
    const notifLoading = document.getElementById('notifLoading');
    const notifList = document.getElementById('notifList');
    const notifEmpty = document.getElementById('notifEmpty');
    const notifBadge = document.getElementById('notifBadge');
    const notifFooter = document.getElementById('notifFooter');
    const profileToggle = document.getElementById('profileToggle');
    const profileMenu = document.getElementById('profileMenu');
    let notifRequestToken = 0;

    function placeMenu(trigger, menu, widthHint) {
        if (!trigger || !menu) return;
        menu.classList.remove('menu-hidden');
        const rect = trigger.getBoundingClientRect();
        const width = widthHint || menu.offsetWidth || 320;
        const top = rect.bottom + 8;
        let left = rect.right - width;
        left = Math.max(8, left);
        if (left + width > window.innerWidth - 8) {
            left = window.innerWidth - width - 8;
        }
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
    }

    function hideMenu(menu, trigger) {
        if (!menu) return;
        menu.classList.add('menu-hidden');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    function hideAll() {
        hideMenu(notifMenu, notifToggle);
        hideMenu(profileMenu, profileToggle);
    }

    function showNotifLoading() {
        if (notifLoading) notifLoading.classList.remove('menu-hidden');
        if (notifList) notifList.classList.add('menu-hidden');
        if (notifEmpty) notifEmpty.classList.add('menu-hidden');
        if (notifFooter) notifFooter.classList.add('menu-hidden');
    }

    function showNotifList(items) {
        if (!notifList) return;
        notifList.innerHTML = '';
        notifList.className = 'notif-list';

        items.forEach((item) => {
            const link = document.createElement('a');
            link.className = 'notif-item';
            link.href = item.open_url && item.open_url.length ? item.open_url : '{{ route('notifications.index') }}';
            link.innerHTML =
                '<span class="notif-item-title">' + (item.title || 'Notification') + '</span>' +
                '<span class="notif-item-message">' + (item.message || '') + '</span>' +
                '<span class="notif-item-time">' + (item.created_at_human || '') + '</span>';
            notifList.appendChild(link);
        });

        if (notifLoading) notifLoading.classList.add('menu-hidden');
        if (notifEmpty) notifEmpty.classList.add('menu-hidden');
        if (notifFooter) notifFooter.classList.remove('menu-hidden');
    }

    function showNotifEmpty() {
        if (notifLoading) notifLoading.classList.add('menu-hidden');
        if (notifList) notifList.classList.add('menu-hidden');
        if (notifEmpty) notifEmpty.classList.remove('menu-hidden');
        if (notifFooter) notifFooter.classList.add('menu-hidden');
    }

    function setNotifBadge(count) {
        if (!notifBadge) return;
        const safeCount = Number.isFinite(Number(count)) ? Math.max(0, Math.trunc(Number(count))) : 0;
        notifBadge.classList.remove('is-on', 'notif-count-badge--2', 'notif-count-badge--3');
        if (safeCount > 0) {
            const label = safeCount > 99 ? '99+' : String(safeCount);
            notifBadge.textContent = label;
            notifBadge.classList.remove('hidden');
            notifBadge.classList.add('is-on');
            if (label.length >= 3) {
                notifBadge.classList.add('notif-count-badge--3');
            } else if (label.length === 2) {
                notifBadge.classList.add('notif-count-badge--2');
            }
            notifBadge.setAttribute(
                'aria-label',
                safeCount > 99
                    ? 'Plus de 99 notifications non lues'
                    : safeCount === 1
                        ? '1 notification non lue'
                        : `${safeCount} notifications non lues`
            );
        } else {
            notifBadge.textContent = '';
            notifBadge.classList.add('hidden');
            notifBadge.removeAttribute('aria-label');
        }
    }

    function loadNotifications() {
        notifRequestToken += 1;
        const token = notifRequestToken;
        showNotifLoading();

        fetch('{{ route('notifications.feed') }}', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then((resp) => resp.ok ? resp.json() : Promise.reject(new Error('fetch_failed')))
            .then((payload) => {
                if (token !== notifRequestToken) return;
                const items = Array.isArray(payload.items) ? payload.items : [];
                const unread = Number(payload.unread_count || 0);
                setNotifBadge(unread);
                if (items.length > 0) {
                    showNotifList(items);
                } else {
                    showNotifEmpty();
                }
            })
            .catch(() => {
                if (token !== notifRequestToken) return;
                showNotifEmpty();
            });
    }

    if (notifToggle && notifMenu) {
        notifToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const willOpen = notifMenu.classList.contains('menu-hidden');
            hideAll();
            if (willOpen) {
                placeMenu(notifToggle, notifMenu, 380);
                notifToggle.setAttribute('aria-expanded', 'true');
                loadNotifications();
            }
        });
    }

    loadNotifications();

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const willOpen = profileMenu.classList.contains('menu-hidden');
            hideAll();
            if (willOpen) {
                placeMenu(profileToggle, profileMenu, 192);
                profileToggle.setAttribute('aria-expanded', 'true');
            }
        });
    }

    [notifMenu, profileMenu].forEach((menu) => {
        if (!menu) return;
        menu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });

    window.addEventListener('resize', function () {
        if (notifMenu && !notifMenu.classList.contains('menu-hidden')) {
            placeMenu(notifToggle, notifMenu, 380);
        }
        if (profileMenu && !profileMenu.classList.contains('menu-hidden')) {
            placeMenu(profileToggle, profileMenu, 192);
        }
    });

    document.addEventListener('click', hideAll);
});
</script>
