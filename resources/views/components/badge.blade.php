@props([
    'variant' => 'neutral',
    'solid' => false,
])

{{--
    Een statuslabel.

    De varianten zijn neutral, success, danger, warning, info en brand.

    `solid` voor het uiterste van een schaal: fataal naast fout, geblokkeerd
    naast open. Zonder dat verschil leest de ergste regel als de rest.
--}}

<span {{ $attributes->class([
    'badge',
    'badge-'.$variant,
    'badge-solid' => $solid,
]) }}>{{ $slot }}</span>
