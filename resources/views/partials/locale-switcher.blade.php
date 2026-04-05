@php
    $variant = $variant ?? 'app';
    $tone = $tone ?? 'dark';
@endphp
@if ($variant === 'login')
    {{-- Styles dans auth/login.blade.php (pas de Tailwind sur cette page) --}}
    <div class="locale-segmented {{ $class ?? '' }}" role="navigation" aria-label="{{ __('ui.locale_label') }}">
        <a href="{{ route('locale.switch', ['locale' => 'fr']) }}"
           lang="fr"
           class="locale-segmented__btn {{ app()->getLocale() === 'fr' ? 'is-active' : '' }}">FR</a>
        <a href="{{ route('locale.switch', ['locale' => 'en']) }}"
           lang="en"
           class="locale-segmented__btn {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">EN</a>
    </div>
@else
    @php
        $isFr = app()->getLocale() === 'fr';
        $isEn = app()->getLocale() === 'en';
        if ($tone === 'dark') {
            $shell = 'inline-flex items-stretch p-[3px] gap-0.5 rounded-full bg-black/25 border border-white/10 shadow-[inset_0_1px_2px_rgba(0,0,0,0.25)] backdrop-blur-sm';
            $linkBase = 'inline-flex min-w-[2.5rem] items-center justify-center rounded-full px-3 py-1.5 text-[0.7rem] font-bold tracking-[0.08em] no-underline transition-colors transition-shadow duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/80 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0a0f15]';
            $linkInactive = 'text-white/65 hover:text-white hover:bg-white/10';
            $linkActive = 'text-white bg-gradient-to-br from-[#00b464] to-[#009d58] shadow-[0_2px_10px_rgba(0,180,100,0.35)] ring-1 ring-white/15';
        } else {
            $shell = 'inline-flex items-stretch p-[3px] gap-0.5 rounded-full bg-slate-200/90 dark:bg-slate-700/80 border border-slate-300/80 dark:border-slate-600 shadow-inner';
            $linkBase = 'inline-flex min-w-[2.5rem] items-center justify-center rounded-full px-3 py-1.5 text-[0.7rem] font-bold tracking-[0.08em] no-underline transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2';
            $linkInactive = 'text-slate-600 hover:text-slate-900 hover:bg-white/80 dark:text-slate-300 dark:hover:text-white dark:hover:bg-white/10';
            $linkActive = 'text-white bg-gradient-to-br from-[#00b464] to-[#009d58] shadow-md ring-1 ring-black/5';
        }
    @endphp
    <div class="locale-switcher {{ $shell }} {{ $class ?? '' }}" role="navigation" aria-label="{{ __('ui.locale_label') }}">
        <a href="{{ route('locale.switch', ['locale' => 'fr']) }}"
           lang="fr"
           class="{{ $linkBase }} {{ $isFr ? $linkActive : $linkInactive }}"
           @if ($isFr) aria-current="true" @endif>FR</a>
        <a href="{{ route('locale.switch', ['locale' => 'en']) }}"
           lang="en"
           class="{{ $linkBase }} {{ $isEn ? $linkActive : $linkInactive }}"
           @if ($isEn) aria-current="true" @endif>EN</a>
    </div>
@endif
