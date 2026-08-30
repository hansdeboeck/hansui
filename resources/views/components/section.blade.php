@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

{{--
    Een kaart met een kop erboven.

    Dit is het gat tussen <x-card> en <x-table>. De eerste is een kaal vlak, de
    tweede tekent zijn eigen kopregel -- en alles daartussen (een formulier, een
    lijst, een blok tekst met een titel erboven) schreef die kopregel zelf. In
    het paneel van streek stond die vorm DRIEENTACHTIG keer, telkens als een
    card-header met een h3 erin, en telkens net anders: de ene met een
    ondertitel, de andere met de knop links van de titel.

    De KOP staat er alleen als er een titel of een actie is. Een kaart zonder
    kop is gewoon een <x-card>, en die hoort er dan ook uit te zien.

    De ACTIES gaan in de slot `actions`: ze staan rechts naast de titel, en op
    een smal scherm eronder.

    `:padding="false"` voor inhoud die zelf tot aan de rand loopt -- een tabel,
    een lijst met eigen scheidingslijnen. Padding raden gaat hier net zo vaak
    mis als bij <x-card>, dus het staat als knop en niet als aanname.
--}}

<section {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($actions))
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-200 px-4 py-3">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-sm font-semibold text-gray-900">{{ $title }}</h2>
                @endif

                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    <div @class(['p-4' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-gray-200 px-4 py-2.5 text-xs text-gray-500">
            {{ $footer }}
        </div>
    @endisset
</section>
