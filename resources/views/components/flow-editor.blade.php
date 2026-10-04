@props([
    'graph',
    'name' => 'flow',
    'form' => null,
    'issues' => [],
    'notes' => [],
    'badges' => [],
    'icon' => 'icon',
    'label' => null,
    'readonly' => false,
    'fill' => false,
    'flush' => false,
])

{{--
    De flow-editor (resources/js/flows/editor.js, op te nemen met
    resources/js/flows.js, en resources/css/flows.css).

        <x-flow-editor :graph="$graph" name="flow" form="automatisatie" :issues="$issues">
            <template data-flow-form="task" data-flow-summary="{title}">
                <x-field name="title" :label="__('Titel')">
                    <input id="title" name="title" class="input" required data-flow-summary>
                </x-field>
            </template>
        </x-flow-editor>

    `graph` is een HansDeBoeck\HansUi\Flows\Graph: de soorten komen mee. `form` is
    het id van het formulier dat het verborgen veld verstuurt: zet de editor
    BUITEN dat formulier (een formulier in een formulier bestaat niet), het
    veld hoort er toch bij. `issues` zijn wat de server zelf vond, per stap
    (Graph::issuesByNode); wat de editor zelf kan zien (een leeg verplicht
    veld, een stap die nergens aan hangt), rekent hij live. `notes` zijn
    zinnen per stap voor in het paneel (een geheim, een uitleg), `badges` een
    kort cijfer per stap op het canvas ("12 wachten hier"). `readonly` toont
    de flow om te bekijken: stappen openen en het canvas verschuiven kan,
    wijzigen niet, en het veld wordt niet verstuurd. `fill` laat de editor tot
    onderaan het venster lopen, voor een pagina die vooral de editor is;
    `flush` zet hem van rand tot rand (geen rand, geen ronde hoeken, niets
    eronder), voor een pagina die alleen de editor is.

    De slot `header` is een balk bovenaan de editor, voor wat de pagina anders
    boven de editor zou zetten (de weg terug, de naam, Bewaren): zo blijft het
    erbij in volledig scherm. De editor zet er zelf de knop Volledig scherm
    achter. `notices` komt onder die balk (een melding, wat er nog ontbreekt).

    De slot `overview` is een tweede weergave naast de flow, voor wat de
    pagina anders onder de editor zou zetten (de laatste keren, de cijfers):

        <x-slot:overview :label="__('Uitvoeringen')" hash="uitvoeringen">...</x-slot:overview>

    In de balk komt dan een keuze tussen de flow en het overzicht; het
    overzicht neemt de plaats van het werkvlak in en scrolt zelf. `hash` is
    het stuk van de url dat het overzicht toont (standaard #overzicht), zodat
    een link of de knop Terug er weer uitkomt.

    In de slot staan de formulieren van de stappen, een <template> per soort.
    Wat erin moet, staat in de README.

    Na een validatiefout toont de editor wat de gebruiker had, niet wat er
    bewaard is: old() wint, als het een geldige flow is.
--}}

@php
    $types = $graph->types();
    $value = $graph->toJson();
    $old = old($name);

    if (is_string($old) && $old !== '') {
        try {
            $value = \HansDeBoeck\HansUi\Flows\Graph::fromJson($old, $types)->toJson();
        } catch (\HansDeBoeck\HansUi\Flows\InvalidGraph) {
            // Wat niet gelezen kan worden, valt terug op wat bewaard is.
        }
    }

    $strings = [
        'next' => __('Daarna'),
        'nothing' => __('Niets: de flow stopt hier'),
        'newStep' => __('Nieuwe stap…'),
        'required' => __('Nog in te vullen: :veld'),
        'unreachable' => __('Deze stap hangt aan geen enkele andere stap.'),
        'startAlone' => __('Nog geen volgende stap.'),
        'startStays' => __('De start blijft: een flow begint altijd ergens.'),
        'issueOne' => __('Een stap vraagt nog aandacht'),
        'issueMany' => __(':aantal stappen vragen nog aandacht'),
        'addAfter' => __('Stap toevoegen na :stap'),
        'addAfterPort' => __('Stap toevoegen na :stap (:uitgang)'),
        'insert' => __('Stap ertussen zetten'),
        'disconnect' => __('Verbinding weghalen'),
        'close' => __('Sluiten'),
        'remove' => __('Verwijderen'),
        'duplicate' => __('Dupliceren'),
        'noResults' => __('Geen stap gevonden.'),
        'added' => __(':stap toegevoegd'),
        'removed' => __(':stap verwijderd'),
        'duplicated' => __(':stap gedupliceerd'),
        'connected' => __('Verbonden'),
        'disconnected' => __('Verbinding weggehaald'),
        'arranged' => __('De stappen zijn geschikt.'),
        'undone' => __('Ongedaan gemaakt'),
        'redone' => __('Opnieuw gedaan'),
        'cycle' => __('Dat kan niet: de flow zou in een kring lopen.'),
        'start' => __('Naar de start kan niets verbonden worden.'),
        'self' => __('Een stap kan niet naar zichzelf.'),
        'port' => __('Die uitgang bestaat niet.'),
        'missing' => __('Die stap bestaat niet meer.'),
        'unknownOption' => __('Bestaat niet meer (:waarde)'),
        'fullscreenOn' => __('Volledig scherm. Escape zet het terug.'),
        'fullscreenOff' => __('Niet meer volledig scherm.'),
    ];

    $uid = 'flow-'.\Illuminate\Support\Str::random(6);
    $paletteId = $uid.'-palette';

    $overview = isset($overview) && $overview->hasActualContent() ? $overview : null;
    $header = isset($header) && $header->hasActualContent() ? $header : null;
    $bar = $header !== null || $overview !== null;
@endphp

<div {{ $attributes->class(['flow']) }}
     data-flow-editor
     data-flow-types="{{ json_encode($types, JSON_UNESCAPED_UNICODE) }}"
     data-flow-strings="{{ json_encode($strings, JSON_UNESCAPED_UNICODE) }}"
     data-flow-issues="{{ json_encode((object) $issues, JSON_UNESCAPED_UNICODE) }}"
     data-flow-notes="{{ json_encode((object) $notes, JSON_UNESCAPED_UNICODE) }}"
     data-flow-badges="{{ json_encode((object) $badges, JSON_UNESCAPED_UNICODE) }}"
     @if ($readonly) data-flow-readonly @endif
     @if ($fill) data-flow-fill @endif
     @if ($flush) data-flow-flush @endif
     @if ($bar) data-flow-header @endif>

    @if ($readonly)
        <input type="hidden" value="{{ $value }}" data-flow-input>
    @else
        <input type="hidden" name="{{ $name }}" value="{{ $value }}" @if ($form) form="{{ $form }}" @endif data-flow-input>
    @endif

    @if ($bar)
        <div class="flow-header">
            <div class="flow-header-row">
                <div class="flow-header-main">{{ $header }}</div>

                @if ($overview)
                    <div class="segmented flow-views" role="group" aria-label="{{ __('Weergave') }}">
                        <button type="button" class="segment" data-flow-view="flow" aria-pressed="true" aria-controls="{{ $uid }}-stage">{{ __('Flow') }}</button>
                        <button type="button" class="segment" data-flow-view="overview" aria-pressed="false" aria-controls="{{ $uid }}-overview">{{ $overview->attributes->get('label') ?? __('Overzicht') }}</button>
                    </div>
                @endif

                <button type="button" class="btn btn-secondary btn-sm flow-fullscreen-button" data-flow-fullscreen aria-pressed="false" aria-label="{{ __('Volledig scherm') }}" title="{{ __('Volledig scherm') }} (F)">
                    <span class="flow-fullscreen-enter">@include('hansui::partials.flow-icon', ['name' => 'fullscreen'])</span>
                    <span class="flow-fullscreen-exit">@include('hansui::partials.flow-icon', ['name' => 'fullscreen-exit'])</span>
                    <span>{{ __('Volledig scherm') }}</span>
                </button>
            </div>

            @if (isset($notices) && $notices->hasActualContent())
                <div class="flow-header-notices">{{ $notices }}</div>
            @endif
        </div>
    @endif

    {{-- Het werkvlak: onder de balk, en wat erin zweeft (de knoppen, het paneel, het palet) rekent vanaf hier. --}}
    <div class="flow-stage" id="{{ $uid }}-stage" data-flow-stage>
        <div class="flow-canvas" data-flow-canvas tabindex="-1" role="region" aria-label="{{ $label ?? __('Flow') }}" aria-describedby="{{ $uid }}-help">
            <div class="flow-world" data-flow-world>
                <svg class="flow-edges" data-flow-edges aria-hidden="true"></svg>
                <div class="flow-nodes" data-flow-nodes></div>
                <div class="flow-overlay" data-flow-overlay></div>
            </div>

            {{-- Wachten is een skelet: de vorm van een flow, tot de editor er is. --}}
            <div class="flow-skeleton" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
            <span class="flow-sr" data-flow-loading>{{ __('Laden…') }}</span>
        </div>

        <div class="flow-toolbar">
            @unless ($readonly)
                <button type="button" class="btn btn-secondary btn-sm" data-flow-add>
                    @include('hansui::partials.flow-icon', ['name' => 'plus'])
                    <span>{{ __('Stap toevoegen') }}</span>
                </button>

                <span class="flow-tools">
                    <button type="button" class="flow-tool" data-flow-undo disabled aria-label="{{ __('Ongedaan maken') }}" title="{{ __('Ongedaan maken') }} (Ctrl+Z)">@include('hansui::partials.flow-icon', ['name' => 'undo'])</button>
                    <button type="button" class="flow-tool" data-flow-redo disabled aria-label="{{ __('Opnieuw doen') }}" title="{{ __('Opnieuw doen') }} (Ctrl+Shift+Z)">@include('hansui::partials.flow-icon', ['name' => 'redo'])</button>
                    <button type="button" class="flow-tool" data-flow-arrange aria-label="{{ __('Schikken') }}" title="{{ __('Schikken') }}">@include('hansui::partials.flow-icon', ['name' => 'arrange'])</button>
                </span>
            @endunless

            <button type="button" class="flow-issue-button" data-flow-issues-button hidden>
                @include('hansui::partials.flow-icon', ['name' => 'warning'])
                <span data-flow-issue-count></span>
            </button>
        </div>

        <div class="flow-zoom flow-tools">
            <button type="button" class="flow-tool" data-flow-zoom-out aria-label="{{ __('Uitzoomen') }}" title="{{ __('Uitzoomen') }}">@include('hansui::partials.flow-icon', ['name' => 'zoom-out'])</button>
            <span class="flow-zoom-label" data-flow-zoom-label aria-hidden="true">100%</span>
            <button type="button" class="flow-tool" data-flow-zoom-in aria-label="{{ __('Inzoomen') }}" title="{{ __('Inzoomen') }}">@include('hansui::partials.flow-icon', ['name' => 'zoom-in'])</button>
            <button type="button" class="flow-tool" data-flow-fit aria-label="{{ __('Alles tonen') }}" title="{{ __('Alles tonen') }}">@include('hansui::partials.flow-icon', ['name' => 'fit'])</button>
            <button type="button" class="flow-tool" data-flow-fullscreen aria-label="{{ __('Volledig scherm') }}" title="{{ __('Volledig scherm') }}">@include('hansui::partials.flow-icon', ['name' => 'fullscreen'])</button>
        </div>

        <aside class="flow-panel" data-flow-panel hidden></aside>

        <div class="flow-palette" data-flow-palette hidden role="dialog" aria-label="{{ __('Stap toevoegen') }}">
            <div class="flow-palette-search">
                <input type="search" class="input" data-flow-search autocomplete="off" form="{{ $uid }}-none"
                       role="combobox" aria-expanded="true" aria-autocomplete="list" aria-controls="{{ $paletteId }}"
                       placeholder="{{ __('Zoek een stap') }}" aria-label="{{ __('Zoek een stap') }}">
            </div>
            <div class="flow-palette-list" id="{{ $paletteId }}" role="listbox" data-flow-palette-list></div>
        </div>
    </div>

    {{-- Het overzicht: verborgen tot iemand het kiest, of tot de url erom vraagt (#overzicht). --}}
    @if ($overview)
        <div {{ $overview->attributes->except(['label', 'hash'])->class(['flow-overview']) }} id="{{ $uid }}-overview" data-flow-overview="{{ $overview->attributes->get('hash') ?? 'overzicht' }}" hidden>
            {{ $overview }}
        </div>
    @endif

    <p class="flow-sr" id="{{ $uid }}-help">{{ $readonly
        ? __('Elke stap is een knop: Enter opent ze. Je kan deze flow bekijken, niet wijzigen.')
        : __('Elke stap is een knop: Enter opent ze, de pijltjes verschuiven ze, Delete haalt ze weg. Wat na een stap komt, kies je bij Daarna.') }}</p>
    <div class="flow-sr" aria-live="polite" data-flow-live></div>

    @foreach ($types as $type)
        <template data-flow-icon="{{ $type->key }}"><x-dynamic-component :component="$icon" :name="$type->icon" size="flow-icon"/></template>
    @endforeach

    @foreach (['plus', 'close', 'warning', 'trash', 'copy'] as $chrome)
        <template data-flow-chrome="{{ $chrome }}">@include('hansui::partials.flow-icon', ['name' => $chrome])</template>
    @endforeach

    {{ $slot }}

    <noscript><p class="flow-noscript">{{ __('De editor heeft JavaScript nodig om een flow te tekenen.') }}</p></noscript>
</div>
