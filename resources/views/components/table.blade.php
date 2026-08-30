@props(['sticky' => true])

{{--
    Een tabel in een kaart.

    Dit deed elk scherm zelf, en de helft vergat de overflow-container: op een
    smal scherm duwde de tabel dan de HELE pagina opzij in plaats van alleen
    zichzelf. Nu schuift wat te breed is binnen zijn eigen kader.

    De KOPREGEL blijft staan bij het scrollen. Op een lijst van vierhonderd
    medewerkers is een kolomkop die na twintig rijen verdwenen is, geen kop meer.
--}}

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    {{--
        Een kop BOVEN de tabel: een titel, en meestal een knop ernaast.

        Die vorm stond tweeentwintig keer met de hand geschreven, telkens als
        een div met een onderrand tussen de kaart en de tabel. Hij staat hier
        BUITEN de scrollcontainer: binnenin zou hij meeschuiven met een brede
        tabel, en dan schuift de titel uit beeld terwijl de kolommen bewegen.
    --}}
    @isset($header)
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-3"
             style="border-color: var(--line)">
            {{ $header }}
        </div>
    @endisset

    {{--
        De scrollcontainer draagt de vastgezette kopregel, niet de pagina.

        Dat is geen stijlkeuze maar de enige vorm die WERKT. Zodra hier
        `overflow-x: auto` staat, rekent de browser `overflow-y` mee om naar
        `auto` -- de CSS-specificatie laat de combinatie zichtbaar/scrollend niet
        toe. Deze div wordt daarmee de scrollcontainer van alles erbinnen, en een
        `position: sticky` binnenin plakt dus aan DEZE div en niet aan het
        scherm. Staat er geen hoogte op, dan scrolt die div nooit, en dan doet de
        sticky-kopregel niets: hij verdwijnt gewoon mee naar boven.

        Vandaar de maximumhoogte. Een korte tabel raakt hem niet aan en gedraagt
        zich als voorheen; een lange krijgt zijn eigen scrollgebied met de
        kolomkoppen erboven. Bij AFDRUKKEN vervalt hij weer, anders drukt een
        dossier af tot waar het toevallig afgesneden was.
    --}}
    <div @class(['overflow-x-auto', 'max-h-[70vh] overflow-y-auto print:max-h-none print:overflow-visible' => $sticky && isset($head)])>
        <table class="min-w-full">
            @isset($head)
                <thead @class(['sticky top-0 z-10' => $sticky]) style="background: var(--surface)">
                    <tr class="border-b" style="border-color: var(--line)">
                        {{ $head }}
                    </tr>
                </thead>
            @endisset

            <tbody class="divide-y" style="border-color: var(--line)">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @isset($footer)
        <div class="border-t px-4 py-2.5 text-xs" style="border-color: var(--line); color: var(--ink-faint)">
            {{ $footer }}
        </div>
    @endisset
</div>
