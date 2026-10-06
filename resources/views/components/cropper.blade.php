@props([
    'src' => null,
    'alt' => '',
    'ratios' => ['free', '1:1', '4:5', '16:9'],
    'ratio' => 'free',
    'name' => 'crop',
    'label' => null,
])

@php
    /*
    | Een beeld bijsnijden: de knoppen voor de verhouding, het beeld met het
    | kader, en vier verborgen velden met de uitsnede in procenten.
    |
    | Het gedrag zit in hansui.js (data-crop); deze component zet alleen de
    | onderdelen bij elkaar in de vorm die dat gedrag verwacht. Wie een andere
    | schikking wil -- de knoppen onder het beeld, de velden elders in het
    | formulier -- schrijft de attributen zelf: ze horen bij de dichtstbijzijnde
    | bijsnijder, dus zolang ze in dezelfde omhulling staan, werkt het.
    |
    | `ratios` is een lijst (`['1:1', '4:5']`) of een lijst met eigen namen
    | (`['4:5' => 'Instagram 4:5']`). "free" is vrij bijsnijden. `ratio` is de
    | verhouding waarmee het begint.
    |
    | Zonder `src` staat er een leeg beeld: een applicatie die het beeld pas
    | kiest als het venster opengaat, zet het src-attribuut dan zelf, en bij
    | het laden begint de bijsnijder opnieuw.
    */
    $keuzes = [];

    foreach ($ratios as $sleutel => $waarde) {
        [$verhouding, $tekst] = is_int($sleutel) ? [$waarde, null] : [$sleutel, $waarde];
        $keuzes[$verhouding] = $tekst ?? ($verhouding === 'free' ? __('Vrij') : $verhouding);
    }

    $uitleg = $label ?? __('Uitsnede. Pijltjes verschuiven, shift en pijltjes vergroten of verkleinen.');
@endphp

<div {{ $attributes->class(['space-y-3']) }}>
    @if ($keuzes !== [])
        <div class="crop-ratios" role="group" aria-label="{{ __('Verhouding') }}">
            @foreach ($keuzes as $verhouding => $tekst)
                <button type="button" class="crop-ratio" data-crop-ratio="{{ $verhouding }}"
                        aria-pressed="{{ (string) $verhouding === (string) $ratio ? 'true' : 'false' }}">{{ $tekst }}</button>
            @endforeach
        </div>
    @endif

    <div class="text-center">
        <div class="crop" data-crop="{{ $uitleg }}">
            <img @if ($src) src="{{ $src }}" @endif alt="{{ $alt }}" draggable="false">
        </div>
    </div>

    <input type="hidden" name="{{ $name }}[x]" data-crop-x>
    <input type="hidden" name="{{ $name }}[y]" data-crop-y>
    <input type="hidden" name="{{ $name }}[width]" data-crop-width>
    <input type="hidden" name="{{ $name }}[height]" data-crop-height>
</div>
