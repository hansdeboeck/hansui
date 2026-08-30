@props([
    'variant' => 'primary',
    'as' => 'button',
    'small' => false,
])

{{--
    De knop.

    De KLASSEN stonden hier al -- .btn en zijn varianten -- maar de component
    niet, en dus schreef elke applicatie hem zelf. Dat is precies de vorm waarin
    dit package eerder uit elkaar liep: dezelfde opmaak, net anders opgeschreven,
    op vier plaatsen tegelijk.

    De varianten zijn primary, secondary, ghost, success, danger en
    danger-outline. Een naam die niet bestaat levert een kale .btn op -- geen
    fout, want een view mag geen witte pagina worden om een tikfout, maar wel
    meteen zichtbaar.

    `as="a"` maakt er een link van. Dat is geen kosmetiek: wat navigeert hoort
    een <a> te zijn, of de middelste muisknop en "open in nieuw tabblad" doen
    niets.
--}}

@php
    $klassen = $attributes->class([
        'btn',
        'btn-'.$variant,
        'btn-sm' => $small,
    ]);
@endphp

@if ($as === 'a')
    <a {{ $klassen }}>{{ $slot }}</a>
@else
    <button {{ $klassen->merge(['type' => 'button']) }}>{{ $slot }}</button>
@endif
