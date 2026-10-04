{{--
    De iconen van de flow-editor zelf (knoppen, waarschuwing), zodat de
    editor ze niet bij de applicatie hoeft te lenen en niet afhangt van welke
    namen <x-icon> in een applicatie kent. De iconen van de stappen komen wel
    van de applicatie (zie de prop `icon` van <x-flow-editor>).

    Zoals <x-icon>: 24 bij 24, lijnvormig, dikte 1.8.
--}}
<svg class="{{ $size ?? 'flow-icon' }}" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    @switch($name)
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('close')
            <path d="M18 6 6 18M6 6l12 12"/>
            @break
        @case('undo')
            <path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>
            @break
        @case('redo')
            <path d="m15 14 5-5-5-5"/><path d="M20 9H9.5a5.5 5.5 0 0 0 0 11H13"/>
            @break
        @case('zoom-in')
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8v6M8 11h6"/>
            @break
        @case('zoom-out')
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M8 11h6"/>
            @break
        @case('fit')
            <path d="M4 9V5a1 1 0 0 1 1-1h4M15 4h4a1 1 0 0 1 1 1v4M20 15v4a1 1 0 0 1-1 1h-4M9 20H5a1 1 0 0 1-1-1v-4"/>
            @break
        @case('fullscreen')
            <path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/>
            @break
        @case('fullscreen-exit')
            <path d="M8 3v3a2 2 0 0 1-2 2H3M21 8h-3a2 2 0 0 1-2-2V3M3 16h3a2 2 0 0 1 2 2v3M16 21v-3a2 2 0 0 1 2-2h3"/>
            @break
        @case('arrange')
            <rect x="3" y="4" width="6" height="5" rx="1.5"/><rect x="15" y="4" width="6" height="5" rx="1.5"/><rect x="15" y="15" width="6" height="5" rx="1.5"/><path d="M9 6.5h6M12 6.5v11h3"/>
            @break
        @case('warning')
            <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>
            @break
        @case('trash')
            <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/>
            @break
        @case('copy')
            <rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>
            @break
        @default
            <rect x="4" y="4" width="16" height="16" rx="3"/>
    @endswitch
</svg>
