@props([
    'variant' => 'neutral',
    'solid' => false,
    'plain' => false,
])

{{--
    Een statuslabel.

    De varianten zijn neutral, success, danger, warning, info en brand.

    `solid` voor het uiterste van een schaal: fataal naast fout, geblokkeerd
    naast open. Zonder dat verschil leest de ergste regel als de rest.

    `plain` haalt het puntje weg. Dat puntje staat er omdat een status ook een
    VORM hoort te dragen voor wie de kleuren niet uit elkaar houdt, dus zet het
    alleen af waar de badge geen status is maar een etiket -- een pakketnaam,
    een rol, een soort.
--}}

<span {{ $attributes->class([
    'badge',
    'badge-'.$variant,
    'badge-solid' => $solid,
    'badge-plain' => $plain,
]) }}>{{ $slot }}</span>
