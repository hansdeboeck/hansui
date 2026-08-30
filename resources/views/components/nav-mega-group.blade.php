@props(['label'])

{{-- Een gerubriceerde groep links in een breed uitklappaneel. --}}
<div>
    <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide" style="color: var(--ink-faint)">{{ $label }}</p>
    {{ $slot }}
</div>
