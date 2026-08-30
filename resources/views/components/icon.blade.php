@props(['name'])

{{--
    De pictogrammen van de applicatie, op een plaats.

    Een vaste lijst en geen bestandsnaam die uit een variabele komt: `name` staat
    in config/navigation.php en in de views, en een @match zonder standaardgeval
    zou een tikfout stil laten verdwijnen. Nu levert een onbekende naam een leeg
    vierkant op dat je meteen ziet staan.

    Alles op 24 bij 24, lijnvormig, dikte 1.8 -- zwaarder oogt in een zijbalk
    als vet en trekt de aandacht weg van waar iets aan de hand is.

    De NAMEN zijn generiek en niet die van een applicatie: `bank` en niet `sbc`,
    `flame` en niet `brandweer`. Een icoon dat naar het domein van een
    applicatie vernoemd is, wordt in de tweede applicatie niet meer gevonden --
    en dan komt dezelfde tekening er onder een tweede naam bij.
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
        @case('briefcase')
            <rect x="3" y="7" width="18" height="13" rx="2"/>
            <path d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7"/>
            <path d="M3 12h18"/>
            @break

        @case('medal')
            <circle cx="12" cy="9" r="5"/>
            <path d="m8.4 13.2-1.4 7.3 5-2.6 5 2.6-1.4-7.3"/>
            @break

        @case('star')
            <path d="m12 4 2.5 5.1 5.5.8-4 3.9.9 5.5-4.9-2.6-4.9 2.6.9-5.5-4-3.9 5.5-.8z"/>
            @break

        @case('shield')
            <path d="M12 3.5 19 6v6c0 4-2.9 7.3-7 8.5C7.9 19.3 5 16 5 12V6z"/>
            <path d="m9.2 12 2 2 3.6-3.6"/>
            @break

        @case('user')
            <circle cx="12" cy="8" r="3.5"/>
            <path d="M5 20c0-3.3 3.1-5.5 7-5.5s7 2.2 7 5.5"/>
            @break

        @case('home')
            <path d="m3.5 10.5 8.5-6.5 8.5 6.5"/>
            <path d="M6 9.5V20h12V9.5"/>
            <path d="M10 20v-5.5h4V20"/>
            @break

        @case('bank')
            <path d="M3.5 9.5 12 4.5l8.5 5"/>
            <path d="M5.5 9.5v8M9.5 9.5v8M14.5 9.5v8M18.5 9.5v8"/>
            <path d="M3.5 20.5h17"/>
            @break

        @case('euro')
            <path d="M17 6.9A6.5 6.5 0 0 0 7.5 12 6.5 6.5 0 0 0 17 17.1"/>
            <path d="M4.5 10.5h8M4.5 13.5h8"/>
            @break

        @case('car')
            <path d="M4 16v-3.2l1.9-4.2a2 2 0 0 1 1.8-1.2h8.6a2 2 0 0 1 1.8 1.2L20 12.8V16"/>
            <path d="M4 16h16"/>
            <circle cx="7.5" cy="17.5" r="1.5"/>
            <circle cx="16.5" cy="17.5" r="1.5"/>
            @break

        @case('truck')
            <path d="M3 6.5h10.5v9H3z"/>
            <path d="M13.5 10h3.6l2.9 3v2.5h-6.5z"/>
            <circle cx="7" cy="17.5" r="1.5"/>
            <circle cx="17" cy="17.5" r="1.5"/>
            @break

        @case('wrench')
            <path d="m9.6 11.4-5 5a2.1 2.1 0 0 0 3 3l5-5"/>
            <path d="M14.5 13a5 5 0 0 0 5.4-6.6l-2.6 2.6-2.3-.6-.6-2.3 2.6-2.6A5 5 0 0 0 10.3 9"/>
            @break

        @case('flame')
            <path d="M12 3s5 4.5 5 9a5 5 0 0 1-10 0c0-1.5.5-2.8 1.2-3.8.3 1.3 1.1 2 2 2.3C9.7 7.5 12 4.5 12 3Z"/>
            @break

        @case('pulse')
            <path d="M3 12.5h4l2-5 3.5 10 2.5-6 1.5 1h4.5"/>
            @break

        @case('bolt')
            <path d="M13 3 5.5 13.5H11l-1 7.5 8-11H12z"/>
            @break

        @case('warning')
            <path d="M12 4 2.8 20h18.4z"/>
            <path d="M12 10v4M12 17h.01"/>
            @break

        @case('info')
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 11v5M12 8h.01"/>
            @break

        @case('question')
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M9.6 9.4A2.5 2.5 0 0 1 14.5 10c0 1.7-2.5 2-2.5 3.5"/>
            <path d="M12 17h.01"/>
            @break

        @case('phone')
            <path d="M7 3.5 9.5 8 7.8 9.9c1 2.2 2.6 3.9 4.8 5l1.9-1.7 4.5 2.5-1.4 3c-.3.7-1 1.1-1.8 1C9.7 18.9 5.1 14.3 4 8.2c-.1-.8.3-1.5 1-1.8z"/>
            @break

        @case('chat')
            <path d="M20 12.5c0 3.6-3.6 6.5-8 6.5-1 0-2-.2-2.9-.5L4 20l1.3-3.3C4.5 15.5 4 14 4 12.5 4 8.9 7.6 6 12 6s8 2.9 8 6.5Z"/>
            @break

        @case('mail')
            <rect x="3" y="5.5" width="18" height="13" rx="2"/>
            <path d="m3.8 7 8.2 6 8.2-6"/>
            @break

        @case('note')
            <path d="M6 3.5h9l4 4v13H6z"/>
            <path d="M15 3.5v4h4"/>
            <path d="M9 12h7M9 16h5"/>
            @break

        @case('file')
            <path d="M6 3.5h8l4.5 4.5v12.5H6z"/>
            <path d="M14 3.5V8h4.5"/>
            @break

        @case('book')
            <path d="M12 6.5C10.5 5 8.5 4.5 4 4.5v13c4.5 0 6.5.5 8 2 1.5-1.5 3.5-2 8-2v-13c-4.5 0-6.5.5-8 2Z"/>
            <path d="M12 6.5v13"/>
            @break

        @case('calendar')
            <rect x="3.5" y="5" width="17" height="15" rx="2"/>
            <path d="M3.5 10h17M8 3.5v3M16 3.5v3"/>
            @break

        @case('clock')
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 7.5V12l3 2"/>
            @break

        @case('image')
            <rect x="3.5" y="4.5" width="17" height="15" rx="2"/>
            <circle cx="9" cy="9.5" r="1.5"/>
            <path d="m4 17 4.5-4.5 3.5 3.5 3-2.5L20 17"/>
            @break

        @case('music')
            <path d="M9 18V6l10-2v12"/>
            <circle cx="6.5" cy="18" r="2.5"/>
            <circle cx="16.5" cy="16" r="2.5"/>
            @break

        @case('monitor')
            <rect x="3" y="4.5" width="18" height="12" rx="2"/>
            <path d="M9 20h6M12 16.5V20"/>
            @break

        @case('wifi')
            <path d="M4.5 9.5a11 11 0 0 1 15 0"/>
            <path d="M7.5 12.8a7 7 0 0 1 9 0"/>
            <path d="M10.4 16a3 3 0 0 1 3.2 0"/>
            <path d="M12 19h.01"/>
            @break

        @case('map')
            <path d="m3.5 6.5 5.5-2 6 2 5.5-2v13l-5.5 2-6-2-5.5 2z"/>
            <path d="M9 4.5v13M15 6.5v13"/>
            @break

        @case('pin')
            <path d="M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11Z"/>
            <circle cx="12" cy="10" r="2.5"/>
            @break

        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break

        @case('close')
            <path d="m6 6 12 12M18 6 6 18"/>
            @break

        @case('pencil')
            <path d="M4 20h4L18 10l-4-4L4 16z"/>
            <path d="m13.5 6.5 4 4"/>
            @break

        @case('trash')
            <path d="M4.5 7h15"/>
            <path d="M9.5 7V5h5v2"/>
            <path d="m6.5 7 1 13h9l1-13"/>
            @break

        @case('arrow-left')
            <path d="M19 12H5"/>
            <path d="m11 6-6 6 6 6"/>
            @break

        @case('arrow-right')
            <path d="M5 12h14"/>
            <path d="m13 6 6 6-6 6"/>
            @break

    @endswitch
</svg>
