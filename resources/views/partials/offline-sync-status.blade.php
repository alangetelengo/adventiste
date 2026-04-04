{{-- Barre d'état hors ligne / file de synchronisation (voir resources/js/offline-sync.js) --}}
<div id="adventiste-offline-bar" class="hidden fixed left-0 right-0 z-[2147483000] border-b border-amber-300/80 bg-amber-100/95 px-4 py-2 text-sm text-amber-950 shadow-sm backdrop-blur-sm dark:border-amber-700/50 dark:bg-amber-950/90 dark:text-amber-100" style="top: 80px;" role="status" aria-live="polite">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
        <span id="adventiste-offline-bar-msg" class="font-medium"></span>
        <button type="button" id="adventiste-offline-sync-btn" class="hidden shrink-0 rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
            Synchroniser maintenant
        </button>
    </div>
</div>
