@props([
    // Er staat iets open in het midden. Op een smal scherm is dat dan het
    // hele scherm, zonder de lijst en zonder de kop.
    'detail' => false,
])

{{--
    Een werkblad: een lijst links, wat je opende in het midden en details
    rechts, en elk scrolt op zich.

    Voor een lijst die je AFWERKT -- een inbox, tickets, bestellingen die
    klaargezet moeten worden. Wie leest, houdt de lijst in beeld; wie klaar is,
    springt naar de volgende zonder terug te moeten.

    Vier slots, en de applicatie kiest de opmaak van elk met de klassen die ze
    meegeeft (meestal `pane`, zie hansui.css):

        <x-panes :detail="$open !== null">
            <x-slot:head class="flex items-center gap-3">…titel en knoppen…</x-slot:head>

            @include('hansui::partials.flash')

            <x-slot:list class="pane" aria-label="Gesprekken">…</x-slot:list>
            <x-slot:main class="pane" aria-label="Gesprek">…</x-slot:main>
            <x-slot:aside class="pane p-4" aria-label="Details">…</x-slot:aside>
        </x-panes>

    WAT IN DE SLOT ZELF STAAT, komt tussen de kop en de panelen en blijft ook
    op een telefoon staan: de plaats voor meldingen. De kop verdwijnt daar
    zodra er iets open is, en een melding dat je antwoord vertrok hoort niet
    mee te verdwijnen.

    De zijkolom staat er pas vanaf 2xl. Daaronder hoort dezelfde inhoud achter
    een knop, in een <x-modal>.
--}}

<div {{ $attributes->class(['panes', 'panes-detail' => $detail]) }}>
    @isset($head)
        <div {{ $head->attributes->class(['panes-head']) }}>{{ $head }}</div>
    @endisset

    {{ $slot }}

    <div class="panes-grid">
        @isset($list)
            <section {{ $list->attributes->class(['panes-list']) }}>{{ $list }}</section>
        @endisset

        @isset($main)
            <section {{ $main->attributes->class(['panes-main']) }}>{{ $main }}</section>
        @endisset

        @isset($aside)
            <aside {{ $aside->attributes->class(['panes-aside']) }}>{{ $aside }}</aside>
        @endisset
    </div>
</div>
