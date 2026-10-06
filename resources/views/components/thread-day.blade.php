@props([
    'date',
    'zone' => null,
])

{{--
    Een nieuwe dag in een gesprek: "Vandaag", "Gisteren", "maandag 3 maart",
    of met het jaar erbij als het niet dit jaar was.

    Een woord en geen datum voor de twee dagen die je het vaakst ziet: wie
    "Gisteren" leest, hoeft niet te rekenen. De tijdzone is die van de lezer,
    anders begint een dag voor iemand in Gent om twee uur 's nachts.
--}}

@php
    $zone ??= config('app.timezone');
    $day = \Illuminate\Support\Carbon::make($date)?->setTimezone($zone)->startOfDay();
    $today = \Illuminate\Support\Carbon::now($zone)->startOfDay();

    $label = match (true) {
        $day === null => '',
        $day->equalTo($today) => __('Vandaag'),
        $day->equalTo($today->copy()->subDay()) => __('Gisteren'),
        $day->year === $today->year => $day->translatedFormat('l j F'),
        default => $day->translatedFormat('j F Y'),
    };
@endphp

@if ($day)
    <p {{ $attributes->class(['thread-day']) }}>{{ $label }}</p>
@endif
