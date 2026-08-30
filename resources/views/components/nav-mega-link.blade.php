@props(['href', 'active' => false, 'label', 'desc' => null])

{{-- Een link in een uitklappaneel, met een regel uitleg eronder. --}}
<a href="{{ $href }}"
   class="block rounded-lg px-3 py-2 transition"
   style="{{ $active ? 'background: var(--surface-sunk)' : '' }}"
   @if ($active) aria-current="page" @endif>
    <span class="block text-sm font-medium" style="color: var(--ink)">{{ $label }}</span>
    @if ($desc)
        <span class="block text-xs" style="color: var(--ink-faint)">{{ $desc }}</span>
    @endif
</a>
