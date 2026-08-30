@props([
    'name' => null,
    'src' => null,
    'size' => 'md',
])

{{--
    Een foto, of de eerste letter als er geen foto is.

    De terugval is het hele punt. In streek heeft ongeveer de helft van de leden
    een avatar uit Discord en de andere helft niet, en elk scherm loste dat zelf
    op: een <img> met een @if ernaast, twee maten die niet gelijk waren, en op
    een paar plaatsen een gebroken plaatje omdat de @else ontbrak.

    De letter komt uit `name` en niet uit een aparte prop: twee bronnen voor
    dezelfde persoon lopen uit elkaar, en dan staat er een N naast "Helena".
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
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}"
         {{ $attributes->class([$maat, 'flex-none rounded-full object-cover']) }}/>
@else
    {{-- aria-hidden op de letter: "H" voorlezen zegt niets. De naam staat
         ernaast in het scherm, en waar dat niet zo is, hoort daar een
         aria-label op de omhulling te staan. --}}
    <span {{ $attributes->class([$maat, 'grid flex-none place-items-center rounded-full bg-gray-100 font-semibold text-gray-600']) }}
          aria-hidden="true">{{ $letter !== '' ? $letter : '?' }}</span>
@endif
