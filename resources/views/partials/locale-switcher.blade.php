@php
    $tone = $tone ?? 'dark';
    $active = 'font-bold underline decoration-2 underline-offset-2';
    $inactive = 'opacity-80 hover:opacity-100';
    $base = $tone === 'dark'
        ? 'text-white/90 hover:text-white'
        : 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white';
@endphp
<div class="locale-switcher flex items-center gap-1 text-sm {{ $class ?? '' }}" role="navigation" aria-label="{{ __('ui.locale_label') }}">
    <a href="{{ route('locale.switch', ['locale' => 'fr']) }}"
       lang="fr"
       class="rounded px-2 py-0.5 transition-opacity {{ $base }} {{ app()->getLocale() === 'fr' ? $active : $inactive }}">FR</a>
    <span class="{{ $tone === 'dark' ? 'text-white/40' : 'text-slate-400' }}" aria-hidden="true">|</span>
    <a href="{{ route('locale.switch', ['locale' => 'en']) }}"
       lang="en"
       class="rounded px-2 py-0.5 transition-opacity {{ $base }} {{ app()->getLocale() === 'en' ? $active : $inactive }}">EN</a>
</div>
