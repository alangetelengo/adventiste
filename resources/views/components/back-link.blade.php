@props([
    'href',
    'label' => 'Retour',
])
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'adventiste-btn-secondary text-sm no-underline inline-flex items-center gap-1.5']) }}>
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    <span>{{ $label }}</span>
</a>
