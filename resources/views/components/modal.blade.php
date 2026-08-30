@props([
    'id',
    'title' => null,
    'size' => 'md',
    'open' => false,
])

{{--
    Een venster boven de pagina, als <dialog>.

    NATIEF en niet nagebouwd. Een eigen div met een waas erachter moet zelf de
    focus vasthouden, Escape afvangen, de rest van de pagina voor een
    schermlezer verbergen en het scrollen eronder stilleggen -- vier dingen die
    een nagebouwd venster stuk voor stuk vergeet. <dialog>.showModal() doet ze
    alle vier, en hansui.js hoeft alleen nog te zeggen wanneer.

    De KNOPPEN staan in de slot en niet in een aparte footer-slot, want een
    formulier moet ze kunnen omsluiten: `<form>` openen in de ene slot en
    sluiten in de andere levert HTML op die niet nest. Vandaar twee klassen die
    de slot zelf gebruikt, met of zonder formulier ertussen:

        <x-modal id="rang" :title="__('Nieuwe rang')">
            <form method="POST" action="{{ route('ranks.store') }}">
                @csrf
                <div class="modal-body">...</div>
                <div class="modal-foot">
                    <x-button variant="secondary" data-modal-close>{{ __('Annuleren') }}</x-button>
                    <x-button type="submit">{{ __('Opslaan') }}</x-button>
                </div>
            </form>
        </x-modal>

    OPENEN doet een knop ergens op de pagina met `data-modal-open="#rang"`.
    `:open` zet het venster meteen open -- dat is wat een formulier met een
    validatiefout nodig heeft: de gebruiker kreeg zijn invoer terug in een
    venster dat na de herlading dicht was.
--}}

<dialog id="{{ $id }}"
        @if ($open) data-modal="open" @else data-modal @endif
        @if ($title) aria-labelledby="{{ $id }}-titel" @endif
        {{ $attributes->class([
            'modal',
            'max-w-sm' => $size === 'sm',
            'max-w-lg' => $size === 'md',
            'max-w-2xl' => $size === 'lg',
            'max-w-4xl' => $size === 'xl',
        ]) }}>
    <div class="modal-head">
        @if ($title)
            <h2 id="{{ $id }}-titel" class="text-sm font-semibold text-gray-900">{{ $title }}</h2>
        @else
            <span></span>
        @endif

        <button type="button" data-modal-close class="link-muted text-lg leading-none"
                aria-label="{{ __('Sluiten') }}">&times;</button>
    </div>

    {{ $slot }}
</dialog>
