@props([
    'cents' => null,
    'amount' => null,
    'currency' => '€',
    'signed' => false,
    'blank' => '—',
])

@php
    /*
    | Een bedrag in Belgische notatie: € 1.234,56.
    |
    | Dit werd in ZESENTACHTIG views met de hand gedaan, verspreid over zes
    | applicaties -- `number_format($x / 100, 2, ',', '.')` en varianten. Elke
    | keer opnieuw de kans om de scheidingstekens om te wisselen, en dat merk je
    | pas als een klant belt over een factuur van € 1,234.
    |
    | CENTEN OF EURO'S, en het verschil moet je zeggen. `:cents` deelt door
    | honderd, `:amount` niet. Een package dat dat raadt aan de grootte van het
    | getal, raadt er ooit een verkeerd.
    |
    | Rekenen doet dit niet. Bedragen worden als integer in centen bewaard --
    | zie de grootboeken -- en deze component is de laatste stap voor het scherm,
    | niet een plaats om iets te vermenigvuldigen.
    */
    $value = $cents !== null ? ((int) $cents) / 100 : (float) ($amount ?? 0);
    $missing = $cents === null && $amount === null;

    $formatted = number_format(abs($value), 2, ',', '.');

    // Het minteken vóór het symbool: "-€ 12,50" en niet "€ -12,50". Dat is hoe
    // een creditnota eruitziet.
    $prefix = $value < 0 ? '-' : ($signed && $value > 0 ? '+' : '');
@endphp

@if ($missing)
    <span {{ $attributes }}>{{ $blank }}</span>
@else
    <span {{ $attributes->class(['tabular-nums']) }}>{{ $prefix }}{{ $currency }}&nbsp;{{ $formatted }}</span>
@endif
