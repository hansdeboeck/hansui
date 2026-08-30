@props([
    'title',
    'subtitle' => null,
    'back' => null,
    'backLabel' => null,
])

{{--
    De kop van een scherm.

    Deze stond in 47 indexschermen los nagebouwd -- een div met justify-between,
    een h1, een p en een knoppenrij -- en dat liep uiteen: de ene had een
    ondertitel, de andere niet, en de terugkeerlink stond nu eens boven en dan
    weer naast de titel.

    De ACTIES gaan in de slot `actions`. Ze staan rechts op een breed scherm en
    onder de titel op een smal, want een knoppenrij die naast een lange titel
    geperst wordt, breekt op het scherm waar hij het hardst nodig is.
--}}

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    @if ($back)
        <a href="{{ $back }}" class="link-muted mb-1 text-xs">
            &larr; {{ $backLabel ?? __('Terug') }}
        </a>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-3">
        <div class="min-w-0">
            <h1 class="text-xl font-semibold text-gray-900">{{ $title }}</h1>

            @if ($subtitle)
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
