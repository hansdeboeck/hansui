{{--
    Een kaart.

    Dun, en dat mag: .card doet het werk. Hij staat hier omdat elke applicatie
    dezelfde <div class="card"> zelf schrijft en er dan net iets bij zet --
    overflow-hidden bij de een, een padding bij de ander -- tot de kaarten
    onderling niet meer gelijk zijn.

    Padding zit er BEWUST niet in. Een kaart met een tabel erin heeft er geen,
    een kaart met tekst wel, en een component die dat raadt, raadt de helft
    verkeerd.
--}}

<div {{ $attributes->merge(['class' => 'card']) }}>
    {{ $slot }}
</div>
