@props([
    'label',
    'value' => null,
    'icon' => null,
    'tone' => 'neutral',
    'hint' => null,
    'href' => null,
    'change' => null,
    'invert' => false,
])

{{--
    Een kerncijfer: een getal met een woord eronder.

    De vorm van elk dashboard, en daarom overal met de hand nagebouwd -- in het
    paneel van streek vijfentwintig keer, in vier maten en drie uitlijningen.
    Dat is precies de soort herhaling die uiteenloopt zonder dat iemand het
    merkt: de ene tegel werd `fs-2hx`, de andere `fs-3`, en naast elkaar in een
    rij zien twee cijfers er dan even belangrijk uit terwijl ze het niet zijn.

    Het GETAL komt uit `value` of uit de slot. Een slot omdat een kerncijfer ook
    een bedrag of een badge mag zijn: <x-stat><x-money :cents="$saldo"/></x-stat>.

    `href` maakt er een link van. Een tegel die ergens heen wijst hoort een <a>
    te zijn -- anders doen de middelste muisknop en "openen in nieuw tabblad"
    niets, en dat is juist wat iemand met een dashboard doet.

    De TOON kleurt alleen de icoon. Het getal blijft inkt: een rij tegels waarin
    elk cijfer zijn eigen kleur heeft, leest als een waarschuwing die er niet is.

    `change` zet het verschil met de vorige periode naast het getal, via
    <x-delta>; `invert` voor een cijfer waar minder beter is. Een cijfer zonder
    vergelijking zegt op een rapport te weinig: "4.210 weergaven" -- is dat
    goed? "+18%" beantwoordt dat.
--}}

@php
    // De tag in een variabele en niet twee keer dezelfde inhoud onder een @if:
    // een tegel die als link net iets anders is opgemaakt dan als vak, is een
    // tegel die in een rij uit de toon valt.
    $tag = $href ? 'a' : 'div';

    $klassen = $attributes->class([
        'card flex items-center gap-3 p-4',
        'transition hover:border-gray-300 hover:bg-gray-50' => $href !== null,
    ]);
@endphp

<{{ $tag }} {{ $href ? $klassen->merge(['href' => $href]) : $klassen }}>
    @if ($icon)
        <span class="tone-{{ $tone }} grid h-10 w-10 flex-none place-items-center rounded-xl">
            <x-hansui::icon :name="$icon" class="h-5 w-5"/>
        </span>
    @endif

    <div class="min-w-0">
        <p class="flex flex-wrap items-baseline gap-x-2">
            <span class="truncate text-xl font-semibold text-gray-900">{{ $value ?? $slot }}</span>
            <x-hansui::delta :change="$change" :invert="$invert"/>
        </p>
        <p class="truncate text-xs text-gray-500">{{ $label }}</p>

        @if ($hint)
            <p class="truncate text-xs text-gray-400">{{ $hint }}</p>
        @endif
    </div>
</{{ $tag }}>
