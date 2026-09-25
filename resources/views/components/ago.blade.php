@props([
    'time' => null,
    'zone' => null,
])

{{--
    Hoe lang geleden, kort: "nu", "12 min", "3 u", "2 d", "17 sep".

    KORT, want het staat in een lijst naast een naam, en "3 hours ago" neemt
    daar een derde van de regel in. De volledige datum staat in de title, voor
    wie het precies wil weten, en in `datetime` voor een machine.

    Na een week de datum en niet "5 w": wie iets van vorige maand zoekt, zoekt
    op de dag en niet op het aantal weken.

    De tijdzone is die van de lezer; geef ze mee als de applicatie ze kent,
    anders is het die van de applicatie.
--}}

@php
    $time = $time === null || $time === '' ? null : \Illuminate\Support\Carbon::make($time);
    $zone ??= config('app.timezone');

    if ($time) {
        $now = \Illuminate\Support\Carbon::now($zone);
        $local = $time->copy()->setTimezone($zone);

        // Met tijdstempels en niet met diffInMinutes(): die telt in Carbon 2
        // standaard absoluut en in Carbon 3 niet, en dit package draait op
        // allebei. Een tijd in de toekomst (een klok die voorloopt) is "nu".
        $minutes = intdiv(max(0, $now->getTimestamp() - $time->getTimestamp()), 60);

        $label = match (true) {
            $minutes < 1 => __('nu'),
            $minutes < 60 => __(':n min', ['n' => $minutes]),
            $minutes < 60 * 24 => __(':n u', ['n' => intdiv($minutes, 60)]),
            $minutes < 60 * 24 * 7 => __(':n d', ['n' => intdiv($minutes, 60 * 24)]),
            $local->year === $now->year => $local->translatedFormat('j M'),
            default => $local->translatedFormat('j M Y'),
        };
    }
@endphp

@if ($time)
    <time datetime="{{ $time->toIso8601String() }}" title="{{ $local->translatedFormat('l j F Y, H:i') }}" {{ $attributes }}>{{ $label }}</time>
@endif
