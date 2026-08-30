@props(['label'])

{{-- Een gerubriceerde groep links in een breed uitklappaneel. --}}
<div {{ $attributes }}>
    <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
    {{ $slot }}
</div>
