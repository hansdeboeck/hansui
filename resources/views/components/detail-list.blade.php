@props(['columns' => 'sm:grid-cols-[160px_1fr]', 'stacked' => false])

{{--
    Een lijst van label-waardeparen.

    Dit stond in ACHTENVIJFTIG views over zes applicaties, telkens als een <dl>
    met dezelfde vier klassen op <dt> en <dd> -- en telkens net anders: de ene
    op 160px, de andere op 180, de derde met uppercase labels.

    Twee vormen, want ze worden allebei gebruikt:

      standaard   label links, waarde rechts, in een raster
      :stacked    label boven de waarde, voor een smalle kolom

    Gebruik met <x-detail-row>:

        <x-detail-list>
            <x-detail-row :label="__('Geboortedatum')">11/05/1988</x-detail-row>
        </x-detail-list>
--}}

<dl {{ $attributes->class([
    'grid gap-x-6 gap-y-3 '.$columns => ! $stacked,
    'space-y-3' => $stacked,
]) }}
    @if ($stacked) data-detail-stacked @endif>
    {{ $slot }}
</dl>
