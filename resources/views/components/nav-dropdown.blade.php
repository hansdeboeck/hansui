@props(['label', 'active' => false, 'wide' => false, 'align' => 'left'])

{{--
    Een geclusterd navigatie-item met een uitklappaneel.

    Byte-identiek in shippingtail en pos -- nul regels verschil. Openen en
    sluiten doet `data-dropdown` uit hansui.js.

    wide  = paneel dat meekrimpt met zijn inhoud (w-max) in plaats van de vaste
            kolom van 18rem. Voor een mega-menu dat zijn groepen naast elkaar
            zet; w-max houdt het paneel smal als een rol maar een deel van de
            groepen mag zien.
    align = 'right' voor items rechts in de balk, anders loopt een breed paneel
            het scherm uit.

    De KNOP staat op een altijd-donkere balk, dus wit blijft wit: on-dark en
    niet text-white, dat laatste kantelt mee met het thema.
--}}
<div class="relative" data-dropdown>
    <button type="button" data-dropdown-toggle aria-haspopup="true"
            @class([
                'nav-item gap-1',
            ])
            @if ($active) aria-current="page" @endif>
        {{ $label }}
        <x-icon name="chevron-down" class="h-4 w-4 opacity-70"/>
    </button>

    {{-- max-h met scroll: een paneel mag nooit onder de onderrand van het
         scherm verdwijnen. --}}
    <div data-dropdown-panel
         @class([
             'absolute top-full z-40 mt-2 hidden max-h-[calc(100vh-6rem)] overflow-y-auto rounded-xl p-2',
             'right-0' => $align === 'right',
             'left-0' => $align !== 'right',
             'w-max' => $wide,
             'w-72' => ! $wide,
         ])
         style="background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow-lg)">
        {{ $slot }}
    </div>
</div>
