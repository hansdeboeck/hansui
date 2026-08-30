@props(['name'])

{{--
    De pictogrammen van de applicatie, op een plaats.

    Een vaste lijst en geen bestandsnaam die uit een variabele komt: `name` staat
    in config/navigation.php en in de views, en een @match zonder standaardgeval
    zou een tikfout stil laten verdwijnen. Nu levert een onbekende naam een leeg
    vierkant op dat je meteen ziet staan.

    Alles op 24 bij 24, lijnvormig, dikte 1.8 -- zwaarder oogt in een zijbalk
    als vet en trekt de aandacht weg van waar iets aan de hand is.
--}}

<svg {{ $attributes->merge(['class' => 'h-4 w-4', 'aria-hidden' => 'true']) }}
     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
     stroke-linecap="round" stroke-linejoin="round">
    @switch($name)
        @case('list')
            <path d="M4 6h16M4 12h10M4 18h7"/>
            @break

        @case('check')
            <path d="M20 7 9 18l-5-5"/>
            @break

        @case('bell')
            <path d="M18 8a6 6 0 1 0-12 0c0 5-2 6-2 6h16s-2-1-2-6"/>
            <path d="M13.7 20a2 2 0 0 1-3.4 0"/>
            @break

        @case('people')
            <circle cx="9" cy="8" r="3.2"/>
            <path d="M3.5 19c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/>
            <path d="M16 11.5a2.8 2.8 0 1 0 0-5.6M17.5 19c0-2.2-.8-3.8-2-4.6"/>
            @break

        @case('grid')
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.4"/>
            <rect x="13.5" y="3.5" width="7" height="7" rx="1.4"/>
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.4"/>
            <rect x="13.5" y="13.5" width="7" height="7" rx="1.4"/>
            @break

        @case('cycle')
            <path d="M20 12a8 8 0 1 1-3.2-6.4"/>
            <path d="M21 4v5h-5"/>
            @break

        @case('folder')
            <path d="M6 3h9l4 4v14H6z"/>
            <path d="M15 3v4h4"/>
            @break

        @case('cap')
            <path d="M12 4 3 8.5l9 4.5 9-4.5z"/>
            <path d="M6.5 10.5V16c0 1.4 2.5 2.5 5.5 2.5s5.5-1.1 5.5-2.5v-5.5"/>
            @break

        @case('bars')
            <path d="M4 20V9m5 11V4m5 16v-7m5 7V7"/>
            @break

        @case('box')
            <path d="M3 8.5 12 4l9 4.5v7L12 20l-9-4.5z"/>
            <path d="M3 8.5 12 13l9-4.5M12 13v7"/>
            @break

        @case('cart')
            <circle cx="9.5" cy="19" r="1.4"/>
            <circle cx="17" cy="19" r="1.4"/>
            <path d="M3 4h2.5l2.2 10.5h10L20 7H6.2"/>
            @break

        @case('chart')
            <path d="M5 20V10m7 10V4m7 16v-7"/>
            @break

        @case('cog')
            <circle cx="12" cy="12" r="3"/>
            <path d="M12 2.5v3M12 18.5v3M2.5 12h3M18.5 12h3M5.2 5.2l2.1 2.1M16.7 16.7l2.1 2.1M18.8 5.2l-2.1 2.1M7.3 16.7l-2.1 2.1"/>
            @break

        @case('search')
            <circle cx="11" cy="11" r="7"/>
            <path d="m20 20-3.5-3.5"/>
            @break

        @case('sun')
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/>
            @break

        @case('moon')
            <path d="M20 13.5A8 8 0 1 1 10.5 4a6.5 6.5 0 0 0 9.5 9.5z"/>
            @break

        @case('rows')
            <path d="M3 6h18M3 12h18M3 18h18"/>
            @break

        @case('leaf')
            <path d="M12 21v-8.25M12 12.75a4.5 4.5 0 0 0-4.5-4.5H6v1.5a4.5 4.5 0 0 0 4.5 4.5h1.5Zm0 0a4.5 4.5 0 0 1 4.5-4.5H18v.75a4.5 4.5 0 0 1-4.5 4.5H12Z"/>
            @break

        @case('chevron-down')
            <path d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            @break

        @case('logout')
            <path d="M15 17l5-5-5-5M20 12H9M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5"/>
            @break

        @case('switch')
            <path d="M7 4 4 7l3 3M4 7h11M17 20l3-3-3-3M20 17H9"/>
            @break
    @endswitch
</svg>
