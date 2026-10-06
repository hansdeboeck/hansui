{{--
    De aanmeldpagina: het formulier links, een foto rechts.

    DE FOTO ZIT NIET IN DIT PACKAGE, ze wordt gevraagd. Een beeld is van de
    applicatie -- shippingtail en pos horen er niet hetzelfde uit te zien -- en
    wat hier binnenkomt, gaat via composer naar alle zes de projecten, ook naar
    wie een ander beeld kiest. De applicatie zet dus zelf:

        @extends('hansui::layouts.auth')

        @section('title', __('Aanmelden'))
        @section('image', asset('images/aanmelden.webp'))

        @section('form')
            <form method="POST" action="{{ route('login') }}">@csrf ... </form>
        @endsection

    Zonder sectie `image` valt de kolom rechts weg en staat het formulier in het
    midden, zoals de foutpagina. Dat is geen noodstand maar de tweede vorm: niet
    elke applicatie heeft een foto, en een halfleeg scherm zou erger zijn dan
    geen foto.

    SECTIES EN GEEN PROPS, anders dan bij de componenten hiernaast. Een layout
    wordt ge-@extend en krijgt dus geen attributen mee; wat de applicatie wil
    invullen, moet langs een sectie. Ze zijn allemaal optioneel op `form` na:
    `title` (de tabtitel), `heading` en `intro` (de kop in de kaart), `logo`,
    `image` en `image-alt`, `footer`, en `assets` voor wie andere ingangen dan
    de Laravel-standaard gebruikt.

    Wat OPMAAK draagt, hoort als blok en niet als waarde. Blade ontsnapt de
    inhoud van `@section('naam', 'waarde')` en die van een blok niet -- op een
    pad of een titel merk je dat nooit, op `logo`, `form` en `assets` des te
    meer:

        @section('assets')
            @vite(['resources/css/aanmelden.css'])
        @endsection

    Bewust zonder layouts/app, om dezelfde reden als de foutpagina: die layout
    verwacht een aangemelde gebruiker, en dat is precies wat hier nog niet zo is.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Aanmelden')) &middot; {{ config('app.name') }}</title>

    {{--
        @vite ACHTER EEN CONTROLE, net als op de foutpagina: ontbreekt het
        manifest, dan gooit @vite zelf een exception en is er van de aanmelding
        niets meer over. Zonder stijlblad blijft dit scherm invulbaar.

        De JS staat er wel bij en op de foutpagina niet, want hier hangt gedrag
        aan: de kruisjes van de flash-balk hieronder komen uit hansui.js. Wie
        andere ingangen heeft dan de twee standaardnamen, vult `assets` in.
    --}}
    @hasSection('assets')
        @yield('assets')
    @else
        @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    @endif
</head>
<body class="surface-dark flex min-h-screen antialiased">
    <div class="flex flex-1 flex-col justify-center px-4 py-10 sm:px-8">
        <div class="mx-auto w-full max-w-md">
            {{-- Hetzelfde merk als op de foutpagina: de beginletter uit
                 config('app.name'), tenzij de applicatie `logo` invult. Zes
                 applicaties, dus hier staat er geen logo vast. --}}
            <div class="mb-6 flex items-center justify-center gap-2.5">
                @hasSection('logo')
                    @yield('logo')
                @else
                    <span class="surface-dark-tint on-dark grid h-10 w-10 place-items-center rounded-lg text-lg font-semibold">
                        {{ mb_strtoupper(mb_substr((string) config('app.name', '?'), 0, 1)) }}
                    </span>
                @endif
                <span class="on-dark text-lg font-semibold">{{ config('app.name') }}</span>
            </div>

            <div class="card p-8">
                <h1 class="text-lg font-semibold text-gray-900">@yield('heading', __('Aanmelden'))</h1>

                @hasSection('intro')
                    <p class="mt-1 text-sm text-gray-500">@yield('intro')</p>
                @endif

                <div class="mt-6">
                    {{--
                        De flash-balk staat HIER en niet in elk aanmeldscherm
                        apart: "de inloggegevens kloppen niet" is de melding die
                        deze pagina het vaakst te tonen heeft.

                        Achter isset($errors), zoals <x-field> het ook doet: die
                        zak komt uit de web-middleware, en buiten een verzoek --
                        een Blade::render(), een test -- bestaat hij niet. Geen
                        zak betekent hier ook geen sessie, dus niets te tonen.
                    --}}
                    @includeWhen(isset($errors), 'hansui::partials.flash')

                    @yield('form')
                </div>
            </div>

            {{-- Zonder sectie `footer` valt de component terug op haar eigen
                 regel; wat er wel in staat, vervangt hem. --}}
            <x-hansui::footer :dark="true">@yield('footer')</x-hansui::footer>
        </div>
    </div>

    @hasSection('image')
        {{--
            Vanaf lg, en daaronder helemaal niet: op een telefoon is een halve
            kolom foto een halve kolom minder formulier.

            `loading="lazy"` is daar het sluitstuk van. Een <img> in een kolom
            die op `display:none` staat wordt door de preloadscanner anders
            gewoon opgehaald -- de telefoon betaalt dan voor een beeld dat ze
            nooit te zien krijgt. Lui geladen gebeurt dat niet, want een
            element zonder vlak kruist nooit het venster. Op een breed scherm
            staat de foto meteen in beeld en laadt ze dus alsnog direct.

            Leeg alt: dit is versiering. Wie er een beeld zet dat iets zegt wat
            elders niet staat, vult `image-alt` in.
        --}}
        <div class="relative hidden lg:block lg:w-1/2">
            <img src="@yield('image')" alt="@yield('image-alt')"
                 loading="lazy" decoding="async"
                 class="absolute inset-0 h-full w-full object-cover">
        </div>
    @endif
</body>
</html>
