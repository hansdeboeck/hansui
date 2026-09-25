@props([
    'name' => null,
    'src' => null,
    'size' => 'md',
    // Een kleur voor de letter: true neemt ze uit de naam, een tekst uit die
    // tekst (een e-mailadres blijft bij de persoon als de naam verandert).
    'tint' => null,
])

{{--
    Een foto, of de eerste letter als er geen foto is.

    De terugval is het hele punt. In streek heeft ongeveer de helft van de leden
    een avatar uit Discord en de andere helft niet, en elk scherm loste dat zelf
    op: een <img> met een @if ernaast, twee maten die niet gelijk waren, en op
    een paar plaatsen een gebroken plaatje omdat de @else ontbrak.

    De letter komt uit `name` en niet uit een aparte prop: twee bronnen voor
    dezelfde persoon lopen uit elkaar, en dan staat er een N naast "Helena".

    MET `tint` KRIJGT DE LETTER EEN KLEUR die bij de persoon blijft: dezelfde
    klant heeft in de lijst en in het gesprek hetzelfde rondje, en in een lijst
    van veertig letters vind je iemand terug aan zijn kleur. Ze komt uit de
    reekskleuren van de grafieken, die al een donkere stand hebben.

    De slot `badge` zet een bolletje rechtsonder: waar iets binnenkwam, of dat
    iemand online is. Met een badge staat de avatar in een omhulling; de
    attributen blijven op de avatar zelf.
--}}

@php
    $maten = [
        'xs' => 'h-6 w-6 text-[0.625rem]',
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-16 w-16 text-lg',
        'xl' => 'h-24 w-24 text-2xl',
    ];

    $maat = $maten[$size] ?? $maten['md'];
    $letter = mb_strtoupper(mb_substr(trim((string) $name), 0, 1));

    $zaad = is_string($tint) && $tint !== '' ? $tint : trim((string) $name);
    $kleur = $tint && ! $src && $zaad !== ''
        ? \HansDeBoeck\HansUi\Chart::series(crc32($zaad) + 1)
        : null;
@endphp

@isset($badge)
    <span class="avatar-wrap">
@endisset

@if ($src)
    {{-- De naam als alt, tenzij de applicatie er zelf een meegeeft: naast
         een naam in het scherm is alt="" beter, anders hoor je hem twee keer. --}}
    <img src="{{ $src }}"
         {{ $attributes->merge(['alt' => (string) $name])->class([$maat, 'flex-none rounded-full object-cover']) }}/>
@else
    @php
        $rondje = $attributes->class([$maat, 'grid flex-none place-items-center rounded-full font-semibold', 'bg-gray-100 text-gray-600' => $kleur === null]);

        if ($kleur !== null) {
            $rondje = $rondje->style([
                "background: color-mix(in oklab, {$kleur} 16%, var(--surface))",
                "color: color-mix(in oklab, {$kleur} 78%, var(--ink))",
            ]);
        }
    @endphp

    {{-- aria-hidden op de letter: "H" voorlezen zegt niets. De naam staat
         ernaast in het scherm, en waar dat niet zo is, hoort daar een
         aria-label op de omhulling te staan. --}}
    <span {{ $rondje }} aria-hidden="true">{{ $letter !== '' ? $letter : '?' }}</span>
@endif

@isset($badge)
        <span {{ $badge->attributes->class(['avatar-badge']) }}>{{ $badge }}</span>
    </span>
@endisset
