@props(['label'])

{{--
    Een regel in een <x-detail-list>.

    De opmaak hangt af van de vorm van de lijst eromheen, en dat wordt hier met
    CSS geregeld in plaats van met een prop: anders moet elke regel herhalen wat
    de lijst al weet, en dan staat er ooit een gestapelde regel in een rasterlijst.
--}}

<dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
<dd class="text-sm text-gray-700">{{ $slot }}</dd>
