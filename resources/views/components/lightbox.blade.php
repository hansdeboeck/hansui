@props([
    'id',
    'group' => '',
    'label' => null,
    'aside' => false,
])

{{--
    Een groot voorbeeld over het hele scherm, met vorige en volgende.

    Het venster staat een keer op de pagina; de links die het openen dragen
    `data-lightbox` met dezelfde groep (zie hansui.js). Wat er getoond wordt,
    komt van de link, dus het venster zelf is leeg tot er iets opengaat:

        <a href="{{ route('foto.show', $foto) }}" data-lightbox="fotos"
           data-lightbox-src="{{ $foto->url() }}" data-lightbox-title="{{ $foto->naam }}">…</a>

        <x-lightbox id="voorbeeld" group="fotos" :label="__('Voorbeeld')">
            <p>Het zijpaneel, of leeg en met data-lightbox-aside per link.</p>
        </x-lightbox>

    De SLOT is het zijpaneel. Leeg laten en geen `data-lightbox-aside` op de
    links: dan is er geen paneel en krijgt het beeld de hele breedte; met
    `:aside="true"` staat het er ook leeg, om per link te vullen. De slot
    `actions` komt in de balk bovenaan, naast de teller.

    Een <dialog>, net als <x-modal>: showModal() houdt de focus binnen, Escape
    sluit, en de rest van de pagina is voor een schermlezer weg zolang het
    open staat. Het vlak rond het beeld is altijd donker; een foto op een
    lichte ondergrond oogt flets, en een foto kantelt niet mee met het thema.
--}}

<dialog id="{{ $id }}" data-modal data-lightbox-view="{{ $group }}"
        aria-label="{{ $label ?? __('Voorbeeld') }}"
        {{ $attributes->class(['lightbox']) }}>
    <div class="lightbox-main">
        <div class="lightbox-bar">
            <span class="lightbox-title" data-lightbox-caption></span>
            <span class="lightbox-count" data-lightbox-count aria-live="polite"></span>

            {{ $actions ?? '' }}

            <button type="button" class="lightbox-btn" data-modal-close aria-label="{{ __('Sluiten') }}">
                <x-hansui::icon name="close" size="h-5 w-5"/>
            </button>
        </div>

        <div class="lightbox-stage" data-lightbox-stage></div>

        <button type="button" class="lightbox-nav lightbox-prev" data-lightbox-prev aria-label="{{ __('Vorige') }}">
            <x-hansui::icon name="chevron-left" size="h-5 w-5"/>
        </button>
        <button type="button" class="lightbox-nav lightbox-next" data-lightbox-next aria-label="{{ __('Volgende') }}">
            <x-hansui::icon name="chevron-right" size="h-5 w-5"/>
        </button>
    </div>

    @if ($aside || trim((string) $slot) !== '')
        <aside class="lightbox-aside" data-lightbox-aside>{{ $slot }}</aside>
    @endif

    <template data-lightbox-fallback>
        <div class="lightbox-fallback">
            <x-hansui::icon name="file" size="h-12 w-12"/>
            <a href="#" target="_blank" rel="noopener" data-lightbox-open class="underline underline-offset-2">
                {{ __('Openen in een nieuw tabblad') }}
            </a>
        </div>
    </template>
</dialog>
