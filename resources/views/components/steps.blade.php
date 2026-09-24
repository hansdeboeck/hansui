@props([
    // [['label' => 'DNS', 'hint' => 'Wacht op je record', 'state' => 'done|current|error|todo'], ...]
    'items' => [],
    'label' => null,
])

{{--
    Waar iets staat in een reeks stappen die het zelf doorloopt: een domein
    dat op zijn DNS wacht en dan op zijn certificaat, een export die klaargezet
    wordt, een koppeling die in drie delen tot stand komt.

    GEEN WIZARD. De gebruiker klikt hier niet door; hij kijkt waar het proces
    staat. Daarom een lijst en geen knoppen, en daarom mag er een stap in
    `error` staan: dat is precies wanneer iemand op dit scherm komt kijken.

    De toestand staat er drie keer: als kleur, als teken in het rondje en als
    tekst voor een schermlezer. Rood en groen alleen zegt niets aan wie ze niet
    uit elkaar houdt.

    Liggend vanaf `sm`, staand op een telefoon: vier stappen naast elkaar op
    390 pixels zijn vier afgekapte woorden.
--}}

@php
    $names = [
        'done' => __('klaar'),
        'current' => __('bezig'),
        'error' => __('probleem'),
        'todo' => __('nog niet'),
    ];
@endphp

<ol {{ $attributes->class(['steps']) }} @if ($label) aria-label="{{ $label }}" @endif>
    @foreach ($items as $item)
        @php
            $state = $item['state'] ?? 'todo';
            $state = isset($names[$state]) ? $state : 'todo';
        @endphp

        <li class="step" data-step="{{ $state }}" @if ($state === 'current') aria-current="step" @endif>
            <span class="step-dot" aria-hidden="true">
                @if ($state === 'done')
                    <x-hansui::icon name="check" size="h-3.5 w-3.5"/>
                @elseif ($state === 'error')
                    !
                @else
                    {{ $loop->iteration }}
                @endif
            </span>

            <span class="min-w-0">
                <span class="step-label">{{ $item['label'] }}</span>
                <span class="sr-only">({{ $names[$state] }})</span>
                @if (! empty($item['hint']))
                    <span class="step-hint">{{ $item['hint'] }}</span>
                @endif
            </span>
        </li>
    @endforeach
</ol>
