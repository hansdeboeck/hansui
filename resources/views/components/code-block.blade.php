@props(['code'])

@php
    /*
    | Een blok tekst dat ergens ANDERS geplakt moet worden: een DSN, een
    | installatieregel, een sleutel.
    |
    | De kopieerknop hoort erbij en niet erna. De waarde is lang, breekt slecht
    | af, en een gemiste letter levert een sleutel op die stil faalt in een
    | andere applicatie -- daar kom je pas achter als er niets binnenkomt.
    |
    | HET ID KOMT UIT DE INHOUD en niet uit een teller of een toevalsgetal.
    | Livewire tekent deze schermen opnieuw, en een id dat bij elke tekening
    | verandert, laat de morph het hele blok vervangen in plaats van het te
    | laten staan. Twee blokken met dezelfde inhoud krijgen zo hetzelfde id --
    | formeel onjuist, praktisch onschadelijk: de knop kopieert dan de tekst die
    | er toch al stond.
    */
    $id = 'hansui-code-'.substr(sha1((string) $code), 0, 10);
@endphp

<div {{ $attributes->class(['relative']) }}>
    <pre id="{{ $id }}"
         class="panel overflow-x-auto py-2 pl-3 pr-24 font-mono text-xs leading-relaxed text-gray-800"><code>{{ $code }}</code></pre>

    {{-- data-copy-target en geen onclick: een inline handler sneuvelt onder een
         strikte CSP, en dan is er een knop die niets doet. --}}
    <x-hansui::button variant="secondary" small
                      class="absolute right-2 top-1.5"
                      data-copy-target="#{{ $id }}"
                      data-copied="{{ __('Gekopieerd') }}">{{ __('Kopiëren') }}</x-hansui::button>
</div>
