@props(['label', 'active' => false, 'wide' => false, 'align' => 'left'])

{{--
    Een geclusterd navigatie-item met een uitklappaneel.

    Byte-identiek in shippingtail en pos -- nul regels verschil. Openen en
    sluiten doet `data-dropdown` uit hansui.js.

    Dat gedrag ONTBRAK toen deze component hier binnenkwam: de opmaak verhuisde
    mee en de listener niet, dus het paneel stond op `hidden` en niets haalde
    dat er ooit af. Een component die byte-identiek overgezet wordt, neemt niet
    vanzelf mee waar hij op leunt.

    wide  = paneel dat meekrimpt met zijn inhoud (w-max) in plaats van de vaste
            kolom van 18rem. Voor een mega-menu dat zijn groepen naast elkaar
            zet; w-max houdt het paneel smal als een rol maar een deel van de
            groepen mag zien.
    align = 'right' voor items rechts in de balk, anders loopt een breed paneel
            het scherm uit.

    De icoon staat er als <x-hansui::icon> en niet als <x-icon>: een applicatie
    mag die korte naam overschrijven -- de provider staat dat uitdrukkelijk toe
    -- en dan zou dit paneel de icoon van DIE applicatie tekenen, die
    `chevron-down` waarschijnlijk niet kent.
--}}
<div {{ $attributes->class(['relative']) }} data-dropdown>
    <button type="button" data-dropdown-toggle class="nav-item gap-1"
            aria-haspopup="true" aria-expanded="false"
            @if ($active) aria-current="page" @endif>
        {{ $label }}
        <x-hansui::icon name="chevron-down" class="h-4 w-4 opacity-70"/>
    </button>

    <div data-dropdown-panel
         @class([
             'dropdown-panel hidden',
             'right-0' => $align === 'right',
             'left-0' => $align !== 'right',
             'w-max' => $wide,
             'w-72' => ! $wide,
         ])>
        {{ $slot }}
    </div>
</div>
