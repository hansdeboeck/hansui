@props([
    'title',
    'hint' => null,
    'filtered' => false,
])

{{--
    Er staat niets.

    Twee gevallen, en het verschil is de helft van de waarde: er is NOG niets
    (dan hoort er een knop te staan om het eerste te maken) of er is niets DAT
    AAN JE FILTER VOLDOET (dan hoort er te staan dat je het filter kan wissen).
    Een scherm dat allebei als "Geen resultaten" toont, laat de gebruiker naar
    een knop zoeken die er niet is -- of naar gegevens die hij zelf heeft
    weggefilterd.
--}}

<div {{ $attributes->merge(['class' => 'empty']) }}>
    <p class="empty-title">{{ $title }}</p>

    @if ($hint)
        <p class="max-w-sm text-sm">{{ $hint }}</p>
    @elseif ($filtered)
        <p class="max-w-sm text-sm">{{ __('Er is niets dat aan uw filter voldoet. Verruim of wis het filter.') }}</p>
    @endif

    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
