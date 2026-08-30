@props([
    'data' => [],
    'title' => null,
])

@php
    /*
    | Een tabel van sleutels en waarden uit een ARRAY.
    |
    | Naast <x-detail-list> en niet erin. Die is slot-gedreven: je schrijft de
    | paren uit, en dat is de juiste vorm zolang je weet welke er zijn. Deze
    | krijgt een array waarvan de sleutels pas bij het tekenen bekend zijn --
    | verzoekheaders, een label-set, wat een SDK meestuurt -- en daar helpt een
    | slot niet.
    |
    | EEN NIVEAU DIEP AFGEVLAKT, verder niet. Een geneste structuur wordt JSON
    | op een regel: dat leest slecht, maar het leest, en een boom uittekenen die
    | vijf niveaus diep kan gaan levert een scherm op dat je moet dichtklappen
    | voor je het kan lezen.
    |
    | Een lege array tekent NIETS -- ook geen kop. Een titel boven een lege
    | lijst is een belofte dat er iets komt.
    */
    $vlak = [];

    foreach ($data as $sleutel => $waarde) {
        $vlak[$sleutel] = match (true) {
            is_bool($waarde) => $waarde ? 'true' : 'false',
            is_scalar($waarde) || $waarde === null => (string) $waarde,
            default => json_encode($waarde, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        };
    }
@endphp

@if ($vlak !== [])
    <div {{ $attributes }}>
        @if ($title)
            <h3 class="mb-2 text-sm font-semibold text-gray-900">{{ $title }}</h3>
        @endif

        <dl class="divide-y divide-gray-200 overflow-hidden rounded-lg text-sm ring-1 ring-gray-200">
            @foreach ($vlak as $sleutel => $waarde)
                <div class="grid grid-cols-1 gap-1 px-3 py-2 sm:grid-cols-3 sm:gap-4">
                    <dt class="font-mono text-xs text-gray-500">{{ $sleutel }}</dt>
                    <dd class="break-all font-mono text-xs text-gray-900 sm:col-span-2">
                        {{ $waarde === '' ? '-' : $waarde }}
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
@endif
