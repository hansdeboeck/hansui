@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

{{--
    Een melding in het scherm zelf.

    Naast `hansui::partials.flash` en niet in plaats daarvan: die leest de
    sessie en hoort in de layout, deze staat waar een scherm zelf iets te
    zeggen heeft -- een uitleg boven een formulier, de foutenzak van een tweede
    formulier in een venster, een waarschuwing bij een gevaarlijke knop.

    De varianten zijn success, danger, warning en info.

    `:dismissible` hangt hem aan `data-flash`, zodat hetzelfde kruisje uit
    hansui.js hem weghaalt. Zet het NIET op een melding die de gebruiker nog
    nodig heeft nadat hij hem gelezen heeft: een uitleg die je kan wegklikken en
    niet kan terughalen, is een uitleg die je een keer kwijtraakt.
--}}

<div @if ($dismissible) data-flash @endif
     {{ $attributes->class(['alert', 'alert-'.$variant]) }}>
    <div class="alert-body">
        @if ($title)
            <p class="mb-1 font-medium">{{ $title }}</p>
        @endif

        {{ $slot }}
    </div>

    @if ($dismissible)
        <button type="button" data-dismiss class="alert-dismiss" aria-label="{{ __('Sluiten') }}">&times;</button>
    @endif
</div>
