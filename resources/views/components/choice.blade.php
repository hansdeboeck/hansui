@props([
    'name',
    'value' => null,
    'label' => null,
    'icon' => null,
    'hint' => null,
    'type' => 'radio',
    'checked' => false,
])

{{--
    Een keuze als tegel: een radioknop of een vinkje dat eruitziet als een kaart.

    Voor een keuze uit een handvol dingen die een pictogram en een woord
    verdienen -- van dienst wisselen, een eenheid kiezen. Een <select> kan dat
    ook, maar dan moet iemand hem eerst openklappen om te zien wat erin zit.

    De invoer blijft een ECHTE radioknop, alleen onzichtbaar: `sr-only` en niet
    `display: none`, want een verborgen veld krijgt geen focus en is met het
    toetsenbord niet te bereiken. De aangevinkte staat komt uit CSS
    (`.choice:has(:checked)`), dus er is geen JavaScript die de klasse moet
    bijhouden -- en dus ook niets dat na een herlading uit de pas kan lopen.
--}}

<label {{ $attributes->merge(['class' => 'choice']) }}>
    <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" class="sr-only" @checked($checked)/>

    @if ($icon)
        <x-hansui::icon :name="$icon" class="h-6 w-6"/>
    @endif

    <span class="text-sm font-medium text-gray-900">{{ $label ?? $slot }}</span>

    @if ($hint)
        <span class="text-xs text-gray-500">{{ $hint }}</span>
    @endif
</label>
