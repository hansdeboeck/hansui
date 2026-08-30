@props(['label'])

{{--
    Een regel in een <x-detail-list>.

    De opmaak hangt af van de vorm van de lijst eromheen, en dat wordt hier met
    CSS geregeld in plaats van met een prop: anders moet elke regel herhalen wat
    de lijst al weet, en dan staat er ooit een gestapelde regel in een rasterlijst.
--}}

<dt class="text-sm font-medium" style="color: var(--ink-faint)">{{ $label }}</dt>
<dd class="text-sm" style="color: var(--ink-soft)">{{ $slot }}</dd>
