{{--
    De opmaak voor elke foutpagina.

    Bewust zonder layouts/app: die verwacht een aangemelde gebruiker en een
    organisatie in context, en juist bij een fout is een van beide er vaak niet.
    Een foutpagina die zelf stukloopt geeft de kale pagina van het framework
    terug, en dat is precies wat hier vermeden wordt.

    Om dezelfde reden staat er geen route() in maar url('/'): op een centraal
    domein bestaat de tenantroute niet, en op een tenanthost wijst / naar het
    dashboard.

    En om diezelfde reden nog een derde keer: het MERK staat hier niet vast.
    Deze pagina wordt door zes applicaties gepubliceerd, dus de standaard is de
    beginletter uit config('app.name'). Wie een echt logo wil, zet het in
    @section('logo') van de eigen foutpagina.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') &middot; {{ config('app.name') }}</title>

    {{--
        @vite ACHTER EEN CONTROLE, en dat is niet overdreven voorzichtig.

        Ontbreekt het manifest -- een deploy die halverwege is, een 503 die juist
        tijdens de build gevraagd wordt -- dan gooit @vite zelf een exception, en
        dan geeft de foutpagina een fout. Precies wat hierboven staat te
        vermijden. Zonder stijlblad blijft de tekst gewoon leesbaar; dat is een
        lelijke pagina, geen kapotte.
    --}}
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="surface-dark relative flex min-h-screen items-center justify-center px-4 antialiased">
    <div class="w-full max-w-md">
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

        <div class="card p-8 text-center">
            <p class="text-4xl font-semibold tracking-tight text-gray-300">@yield('code')</p>
            <h1 class="mt-3 text-lg font-semibold text-gray-900">@yield('title')</h1>
            <p class="mt-2 text-sm text-gray-500">@yield('message')</p>

            <div class="mt-6 flex flex-col gap-2">
                @hasSection('action')
                    @yield('action')
                @else
                    <a href="{{ url('/') }}" class="btn btn-primary w-full">{{ __('Terug naar het begin') }}</a>
                @endif
            </div>
        </div>

        <p class="on-dark-soft mt-6 text-center text-xs">
            &copy; {{ date('Y') }} @yield('owner', 'deboeck.dev'). {{ __('Alle rechten voorbehouden.') }}
        </p>
    </div>
</body>
</html>
