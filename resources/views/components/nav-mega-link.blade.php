@props(['href', 'active' => false, 'label', 'desc' => null])

{{-- Een link in een uitklappaneel, met een regel uitleg eronder. --}}
<a href="{{ $href }}"
   {{ $attributes->class(['block rounded-lg px-3 py-2 transition', 'bg-gray-100' => $active]) }}
   @if ($active) aria-current="page" @endif>
    <span class="block text-sm font-medium text-gray-900">{{ $label }}</span>
    @if ($desc)
        <span class="block text-xs text-gray-500">{{ $desc }}</span>
    @endif
</a>
